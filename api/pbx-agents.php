<?php
require_once dirname(__DIR__) . '/src/config.php';
require_once dirname(__DIR__) . '/src/Database.php';
require_once dirname(__DIR__) . '/src/helpers.php';
require_once dirname(__DIR__) . '/src/auth.php';

require_auth_api();
$method = $_SERVER['REQUEST_METHOD'];
$id     = (int)($_GET['id'] ?? 0);

if ($method === 'GET') {
    json_response(Database::query('SELECT * FROM pbx_users ORDER BY name'));
}
if ($method === 'POST') {
    $b = get_json_body();
    if (empty($b['name'])) error_response('name required');
    $isActive = isset($b['isActive']) ? (int)(bool)$b['isActive'] : 1;
    $newId = Database::insert(
        'INSERT INTO pbx_users (name, extension, email, role, is_active, notes) VALUES (?,?,?,?,?,?)',
        [$b['name'], $b['extension'] ?? null, $b['email'] ?? null, $b['role'] ?? 'agent', $isActive, $b['notes'] ?? null]
    );
    json_response(Database::row('SELECT * FROM pbx_users WHERE id=?', [$newId]), 201);
}
if ($method === 'PUT' && $id) {
    $b = get_json_body();
    $isActive = isset($b['isActive']) ? (int)(bool)$b['isActive'] : 1;
    Database::execute(
        'UPDATE pbx_users SET name=?,extension=?,email=?,role=?,is_active=?,notes=?,updated_at=NOW() WHERE id=?',
        [$b['name'], $b['extension'] ?? null, $b['email'] ?? null, $b['role'] ?? 'agent', $isActive, $b['notes'] ?? null, $id]
    );
    json_response(Database::row('SELECT * FROM pbx_users WHERE id=?', [$id]));
}
if ($method === 'DELETE' && $id) {
    Database::execute('DELETE FROM pbx_users WHERE id=?', [$id]);
    json_response(['ok' => true]);
}
error_response('Not found', 404);
