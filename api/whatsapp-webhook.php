<?php
/**
 * WhatsApp Cloud API Webhook
 *
 * GET  — Meta webhook verification handshake
 * POST — Incoming message / status update receipt
 *
 * This endpoint does NOT require dashboard auth — Meta calls it externally.
 */
require_once dirname(__DIR__) . '/src/config.php';
require_once dirname(__DIR__) . '/src/Database.php';
require_once dirname(__DIR__) . '/src/helpers.php';
require_once dirname(__DIR__) . '/src/Settings.php';

$method = $_SERVER['REQUEST_METHOD'];

// ── GET: Webhook verification ─────────────────────────────────────────────────
if ($method === 'GET') {
    $mode      = $_GET['hub_mode']          ?? '';
    $challenge = $_GET['hub_challenge']     ?? '';
    $token     = $_GET['hub_verify_token']  ?? '';
    $expected  = Settings::get('whatsapp.verify_token');

    if ($mode === 'subscribe' && $expected !== '' && $token === $expected) {
        http_response_code(200);
        echo $challenge;
    } else {
        http_response_code(403);
        echo 'Forbidden';
    }
    exit;
}

// ── POST: Incoming events from Meta ──────────────────────────────────────────
if ($method === 'POST') {
    $raw     = file_get_contents('php://input');
    $payload = json_decode($raw, true);

    if (!$payload || ($payload['object'] ?? '') !== 'whatsapp_business_account') {
        http_response_code(200); // Always 200 to Meta
        echo 'ok';
        exit;
    }

    foreach ($payload['entry'] ?? [] as $entry) {
        foreach ($entry['changes'] ?? [] as $change) {
            $val = $change['value'] ?? [];

            // ── Incoming messages ──────────────────────────────────────────
            foreach ($val['messages'] ?? [] as $msg) {
                $from    = $msg['from']       ?? '';
                $wamid   = $msg['id']         ?? '';
                $type    = $msg['type']       ?? 'text';
                $body    = $msg['text']['body'] ?? ($msg['caption'] ?? '');
                $mediaUrl = null;

                // Extract contact name from metadata
                $contactName = null;
                foreach ($val['contacts'] ?? [] as $c) {
                    if (($c['wa_id'] ?? '') === $from) {
                        $contactName = $c['profile']['name'] ?? null;
                        break;
                    }
                }

                // Upsert conversation
                if (DB_DRIVER === 'pgsql') {
                    Database::execute(
                        "INSERT INTO whatsapp_conversations
                           (contact_number, contact_name, status, unread_count, last_message_at, last_message_body)
                         VALUES (?, ?, 'open', 1, NOW(), ?)
                         ON CONFLICT (contact_number) DO UPDATE
                         SET contact_name    = COALESCE(EXCLUDED.contact_name, whatsapp_conversations.contact_name),
                             unread_count    = whatsapp_conversations.unread_count + 1,
                             last_message_at = NOW(),
                             last_message_body = EXCLUDED.last_message_body,
                             status          = CASE WHEN whatsapp_conversations.status = 'closed' THEN 'open'
                                                    ELSE whatsapp_conversations.status END,
                             updated_at      = NOW()",
                        [$from, $contactName, $body]
                    );
                } else {
                    Database::execute(
                        "INSERT INTO whatsapp_conversations
                           (contact_number, contact_name, status, unread_count, last_message_at, last_message_body)
                         VALUES (?, ?, 'open', 1, NOW(), ?)
                         ON DUPLICATE KEY UPDATE
                           contact_name     = COALESCE(VALUES(contact_name), contact_name),
                           unread_count     = unread_count + 1,
                           last_message_at  = NOW(),
                           last_message_body = VALUES(last_message_body),
                           status           = IF(status='closed','open',status),
                           updated_at       = NOW()",
                        [$from, $contactName, $body]
                    );
                }

                $conv = Database::row('SELECT id FROM whatsapp_conversations WHERE contact_number = ?', [$from]);
                if ($conv) {
                    Database::execute(
                        'INSERT INTO whatsapp_messages
                           (conversation_id, wamid, direction, message_type, body, media_url, status, sender_name)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
                        [$conv['id'], $wamid, 'inbound', $type, $body, $mediaUrl, 'received', $contactName]
                    );
                }
            }

            // ── Status updates ─────────────────────────────────────────────
            foreach ($val['statuses'] ?? [] as $status) {
                $wamid  = $status['id']     ?? '';
                $newSt  = $status['status'] ?? '';
                if ($wamid && $newSt) {
                    Database::execute(
                        'UPDATE whatsapp_messages SET status = ? WHERE wamid = ?',
                        [$newSt, $wamid]
                    );
                }
            }
        }
    }

    http_response_code(200);
    echo 'ok';
    exit;
}

http_response_code(405);
echo 'Method Not Allowed';
