<?php
require_once dirname(__DIR__) . '/src/config.php';
require_once dirname(__DIR__) . '/src/Database.php';
require_once dirname(__DIR__) . '/src/helpers.php';
require_once dirname(__DIR__) . '/src/auth.php';

require_auth_api();
$method = $_SERVER['REQUEST_METHOD'];
$id     = (int)($_GET['id'] ?? 0);

if ($method === 'GET') {
    json_response(Database::query('SELECT * FROM call_queues ORDER BY name'));
}
if ($method === 'POST') {
    $b = get_json_body();
    if (empty($b['name'])) error_response('name required');
    $newId = Database::insert(
        'INSERT INTO call_queues (name, strategy, max_wait_time, notes) VALUES (?,?,?,?)',
        [$b['name'], $b['strategy'] ?? 'ringall', (int)($b['maxWaitTime'] ?? 60), $b['notes'] ?? null]
    );
    json_response(Database::row('SELECT * FROM call_queues WHERE id = ?', [$newId]), 201);
}
if ($method === 'PUT' && $id) {
    $b = get_json_body();
    Database::execute(
        'UPDATE call_queues SET name=?, strategy=?, max_wait_time=?, notes=?, updated_at=NOW() WHERE id=?',
        [$b['name'], $b['strategy'], (int)($b['maxWaitTime'] ?? 60), $b['notes'] ?? null, $id]
    );
    json_response(Database::row('SELECT * FROM call_queues WHERE id = ?', [$id]));
}
if ($method === 'DELETE' && $id) {
    Database::execute('DELETE FROM call_queues WHERE id = ?', [$id]);
    json_response(['ok' => true]);
}
error_response('Not found', 404);
