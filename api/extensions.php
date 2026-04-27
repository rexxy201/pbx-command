<?php
require_once dirname(__DIR__) . '/src/config.php';
require_once dirname(__DIR__) . '/src/Database.php';
require_once dirname(__DIR__) . '/src/helpers.php';
require_once dirname(__DIR__) . '/src/auth.php';

require_auth_api();

$method = $_SERVER['REQUEST_METHOD'];
$id     = (int)($_GET['id'] ?? 0);

if ($method === 'GET') {
    $rows = Database::query('SELECT * FROM extensions ORDER BY number');
    json_response($rows);
}

if ($method === 'POST') {
    $b = get_json_body();
    if (empty($b['number']) || empty($b['name'])) error_response('number and name required');
    $newId = Database::insert(
        'INSERT INTO extensions (number, name, type, status, notes) VALUES (?,?,?,?,?)',
        [$b['number'], $b['name'], $b['type'] ?? 'customer_support', $b['status'] ?? 'unregistered', $b['notes'] ?? null]
    );
    json_response(Database::row('SELECT * FROM extensions WHERE id = ?', [$newId]), 201);
}

if ($method === 'PUT' && $id) {
    $b = get_json_body();
    Database::execute(
        'UPDATE extensions SET number=?, name=?, type=?, status=?, notes=?, updated_at=NOW() WHERE id=?',
        [$b['number'], $b['name'], $b['type'], $b['status'], $b['notes'] ?? null, $id]
    );
    json_response(Database::row('SELECT * FROM extensions WHERE id = ?', [$id]));
}

if ($method === 'DELETE' && $id) {
    Database::execute('DELETE FROM extensions WHERE id = ?', [$id]);
    json_response(['ok' => true]);
}

error_response('Not found', 404);
