<?php
require_once dirname(__DIR__) . '/src/config.php';
require_once dirname(__DIR__) . '/src/Database.php';
require_once dirname(__DIR__) . '/src/helpers.php';
require_once dirname(__DIR__) . '/src/auth.php';
require_once dirname(__DIR__) . '/src/Settings.php';

require_auth_api();
$method = $_SERVER['REQUEST_METHOD'];
$convId = (int)($_GET['conversation_id'] ?? 0);

if ($method === 'GET') {
    if (!$convId) error_response('conversation_id required');

    // Mark messages as read by resetting unread count
    Database::execute(
        'UPDATE whatsapp_conversations SET unread_count = 0, updated_at = NOW() WHERE id = ?',
        [$convId]
    );

    $msgs = Database::query(
        'SELECT * FROM whatsapp_messages WHERE conversation_id = ? ORDER BY created_at ASC LIMIT 200',
        [$convId]
    );
    json_response($msgs);
}

if ($method === 'POST') {
    $b = get_json_body();
    if (!$convId && !empty($b['conversationId'])) $convId = (int)$b['conversationId'];
    if (!$convId) error_response('conversation_id required');

    $body = trim($b['body'] ?? '');
    if ($body === '') error_response('body required');

    $conv = Database::row('SELECT * FROM whatsapp_conversations WHERE id = ?', [$convId]);
    if (!$conv) error_response('Conversation not found', 404);

    $wa = Settings::whatsapp();
    if (!Settings::isWhatsappConfigured()) {
        error_response('WhatsApp API not configured. Go to System Settings → WhatsApp.', 503);
    }

    $to     = $conv['contact_number'];
    $apiVer = $wa['api_version'];
    $phoneId = $wa['phone_number_id'];
    $token   = $wa['access_token'];

    // Send via WhatsApp Cloud API
    $payload = json_encode([
        'messaging_product' => 'whatsapp',
        'recipient_type'    => 'individual',
        'to'                => $to,
        'type'              => 'text',
        'text'              => ['preview_url' => false, 'body' => $body],
    ]);

    $ctx = stream_context_create([
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/json\r\nAuthorization: Bearer $token\r\n",
            'content' => $payload,
            'timeout' => 10,
            'ignore_errors' => true,
        ]
    ]);
    $url      = "https://graph.facebook.com/{$apiVer}/{$phoneId}/messages";
    $response = @file_get_contents($url, false, $ctx);
    $httpCode = 0;
    if (isset($http_response_header[0])) {
        preg_match('/\d{3}/', $http_response_header[0], $m);
        $httpCode = (int)($m[0] ?? 0);
    }
    $resp = json_decode($response, true) ?? [];

    if ($httpCode < 200 || $httpCode >= 300) {
        $errMsg = $resp['error']['message'] ?? 'WhatsApp API error';
        error_response($errMsg, 502);
    }

    $wamid = $resp['messages'][0]['id'] ?? null;

    // Save outbound message
    $msgId = Database::insert(
        'INSERT INTO whatsapp_messages
           (conversation_id, wamid, direction, message_type, body, status, sender_name)
         VALUES (?, ?, ?, ?, ?, ?, ?)',
        [$convId, $wamid, 'outbound', 'text', $body, 'sent', $b['senderName'] ?? 'Agent']
    );

    // Update conversation last message
    Database::execute(
        'UPDATE whatsapp_conversations
         SET last_message_at = NOW(), last_message_body = ?, updated_at = NOW()
         WHERE id = ?',
        [$body, $convId]
    );

    json_response(Database::row('SELECT * FROM whatsapp_messages WHERE id = ?', [$msgId]), 201);
}

error_response('Method not allowed', 405);
