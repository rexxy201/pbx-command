<?php
require_once dirname(__DIR__) . '/src/config.php';
require_once dirname(__DIR__) . '/src/Database.php';
require_once dirname(__DIR__) . '/src/helpers.php';
require_once dirname(__DIR__) . '/src/auth.php';

require_auth_api();
$method = $_SERVER['REQUEST_METHOD'];
$id     = (int)($_GET['id'] ?? 0);

if ($method === 'GET') {
    json_response(Database::query('SELECT * FROM ivr_menus ORDER BY name'));
}
if ($method === 'POST') {
    $b = get_json_body();
    if (empty($b['name'])) error_response('name required');
    $newId = Database::insert(
        'INSERT INTO ivr_menus (name, description, timeout, invalid_retry_count) VALUES (?,?,?,?)',
        [$b['name'], $b['description'] ?? null, (int)($b['timeout'] ?? 5), (int)($b['invalidRetryCount'] ?? 3)]
    );
    json_response(Database::row('SELECT * FROM ivr_menus WHERE id=?', [$newId]), 201);
}
if ($method === 'PUT' && $id) {
    $b = get_json_body();
    Database::execute(
        'UPDATE ivr_menus SET name=?, description=?, timeout=?, invalid_retry_count=?, updated_at=NOW() WHERE id=?',
        [$b['name'], $b['description'] ?? null, (int)($b['timeout'] ?? 5), (int)($b['invalidRetryCount'] ?? 3), $id]
    );
    json_response(Database::row('SELECT * FROM ivr_menus WHERE id=?', [$id]));
}
if ($method === 'DELETE' && $id) {
    Database::execute('DELETE FROM ivr_menus WHERE id=?', [$id]);
    json_response(['ok' => true]);
}
error_response('Not found', 404);
