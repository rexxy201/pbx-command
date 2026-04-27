<?php
require_once dirname(__DIR__) . '/src/config.php';
require_once dirname(__DIR__) . '/src/Database.php';
require_once dirname(__DIR__) . '/src/helpers.php';
require_once dirname(__DIR__) . '/src/auth.php';

require_auth_api();
$method = $_SERVER['REQUEST_METHOD'];
$id     = (int)($_GET['id'] ?? 0);

if ($method === 'GET') {
    json_response(Database::query('SELECT * FROM ring_groups ORDER BY name'));
}
if ($method === 'POST') {
    $b = get_json_body();
    if (empty($b['name'])) error_response('name required');
    $newId = Database::insert(
        'INSERT INTO ring_groups (name, extension_number, strategy, ring_time, notes) VALUES (?,?,?,?,?)',
        [$b['name'], $b['extensionNumber'] ?? null, $b['strategy'] ?? 'ringall', (int)($b['ringTime'] ?? 20), $b['notes'] ?? null]
    );
    json_response(Database::row('SELECT * FROM ring_groups WHERE id = ?', [$newId]), 201);
}
if ($method === 'PUT' && $id) {
    $b = get_json_body();
    Database::execute(
        'UPDATE ring_groups SET name=?, extension_number=?, strategy=?, ring_time=?, notes=?, updated_at=NOW() WHERE id=?',
        [$b['name'], $b['extensionNumber'] ?? null, $b['strategy'], (int)($b['ringTime'] ?? 20), $b['notes'] ?? null, $id]
    );
    json_response(Database::row('SELECT * FROM ring_groups WHERE id = ?', [$id]));
}
if ($method === 'DELETE' && $id) {
    Database::execute('DELETE FROM ring_groups WHERE id = ?', [$id]);
    json_response(['ok' => true]);
}
error_response('Not found', 404);
