<?php
require_once dirname(__DIR__) . '/src/config.php';
require_once dirname(__DIR__) . '/src/Database.php';
require_once dirname(__DIR__) . '/src/helpers.php';
require_once dirname(__DIR__) . '/src/auth.php';

require_auth_api();
$method = $_SERVER['REQUEST_METHOD'];
$id     = (int)($_GET['id'] ?? 0);

if ($method === 'GET' && !$id) {
    // List conversations ordered by last activity
    $convs = Database::query(
        "SELECT id, contact_number, contact_name, assigned_agent, status,
                unread_count, last_message_at, last_message_body
         FROM whatsapp_conversations
         ORDER BY COALESCE(last_message_at, created_at) DESC
         LIMIT 100"
    );
    json_response($convs);
}

if ($method === 'GET' && $id) {
    $conv = Database::row('SELECT * FROM whatsapp_conversations WHERE id = ?', [$id]);
    if (!$conv) error_response('Not found', 404);
    json_response($conv);
}

if ($method === 'PUT' && $id) {
    $b = get_json_body();
    $fields = [];
    $params = [];
    if (array_key_exists('status', $b))          { $fields[] = 'status = ?';          $params[] = $b['status']; }
    if (array_key_exists('assignedAgent', $b))   { $fields[] = 'assigned_agent = ?';  $params[] = $b['assignedAgent']; }
    if (array_key_exists('contactName', $b))     { $fields[] = 'contact_name = ?';    $params[] = $b['contactName']; }
    if (array_key_exists('unreadCount', $b))     { $fields[] = 'unread_count = ?';    $params[] = (int)$b['unreadCount']; }

    if ($fields) {
        $fields[] = 'updated_at = NOW()';
        $params[] = $id;
        Database::execute(
            'UPDATE whatsapp_conversations SET ' . implode(', ', $fields) . ' WHERE id = ?',
            $params
        );
    }
    json_response(Database::row('SELECT * FROM whatsapp_conversations WHERE id = ?', [$id]));
}

error_response('Not found', 404);
