<?php
require_once dirname(__DIR__) . '/src/config.php';
require_once dirname(__DIR__) . '/src/Database.php';
require_once dirname(__DIR__) . '/src/helpers.php';
require_once dirname(__DIR__) . '/src/auth.php';

require_auth_api();
$method = $_SERVER['REQUEST_METHOD'];
$id     = (int)($_GET['id'] ?? 0);

if ($method === 'GET') {
    json_response(Database::query('SELECT id,name,email,role,status,last_login_at,created_at FROM dashboard_users ORDER BY name'));
}
if ($method === 'POST') {
    $b = get_json_body();
    if (empty($b['name']) || empty($b['email']) || empty($b['role'])) error_response('name, email, and role required');
    if (!empty($b['password'])) {
        $hash = password_hash($b['password'], PASSWORD_DEFAULT);
    } else {
        $hash = null;
    }
    $newId = Database::insert(
        'INSERT INTO dashboard_users (name, email, role, status, password_hash) VALUES (?,?,?,?,?)',
        [trim($b['name']),strtolower(trim($b['email'])),$b['role'],$b['status']??'active',$hash]
    );
    json_response(Database::row('SELECT id,name,email,role,status,created_at FROM dashboard_users WHERE id=?',[$newId]),201);
}
if ($method === 'PUT' && $id) {
    $b = get_json_body();
    $updates = ['name=?','email=?','role=?','status=?','updated_at=NOW()'];
    $params  = [trim($b['name']),strtolower(trim($b['email'])),$b['role'],$b['status']??'active'];
    if (!empty($b['password'])) {
        $updates[] = 'password_hash=?';
        $params[]  = password_hash($b['password'], PASSWORD_DEFAULT);
    }
    $params[] = $id;
    Database::execute('UPDATE dashboard_users SET '.implode(',',$updates).' WHERE id=?', $params);
    json_response(Database::row('SELECT id,name,email,role,status,created_at FROM dashboard_users WHERE id=?',[$id]));
}
if ($method === 'DELETE' && $id) {
    Database::execute('DELETE FROM dashboard_users WHERE id=?', [$id]);
    json_response(['ok'=>true]);
}
error_response('Not found', 404);
