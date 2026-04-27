<?php
require_once dirname(__DIR__) . '/src/config.php';
require_once dirname(__DIR__) . '/src/Database.php';
require_once dirname(__DIR__) . '/src/helpers.php';
require_once dirname(__DIR__) . '/src/auth.php';

require_auth_api();
$method = $_SERVER['REQUEST_METHOD'];
$id     = (int)($_GET['id'] ?? 0);

if ($method === 'GET') {
    json_response(Database::query('SELECT * FROM time_conditions ORDER BY name'));
}
if ($method === 'POST') {
    $b = get_json_body();
    if (empty($b['name'])) error_response('name required');
    $newId = Database::insert(
        'INSERT INTO time_conditions (name, timezone, open_time, close_time, open_days, is_active, notes) VALUES (?,?,?,?,?,?,?)',
        [$b['name'], $b['timezone'] ?? 'Africa/Lagos', $b['openTime'] ?? '08:00', $b['closeTime'] ?? '18:00', $b['openDays'] ?? 'Mon-Fri', (bool)($b['isActive'] ?? true), $b['notes'] ?? null]
    );
    json_response(Database::row('SELECT * FROM time_conditions WHERE id=?', [$newId]), 201);
}
if ($method === 'PUT' && $id) {
    $b = get_json_body();
    Database::execute(
        'UPDATE time_conditions SET name=?, timezone=?, open_time=?, close_time=?, open_days=?, is_active=?, notes=?, updated_at=NOW() WHERE id=?',
        [$b['name'], $b['timezone'] ?? 'Africa/Lagos', $b['openTime'] ?? '08:00', $b['closeTime'] ?? '18:00', $b['openDays'] ?? 'Mon-Fri', (bool)($b['isActive'] ?? true), $b['notes'] ?? null, $id]
    );
    json_response(Database::row('SELECT * FROM time_conditions WHERE id=?', [$id]));
}
if ($method === 'DELETE' && $id) {
    Database::execute('DELETE FROM time_conditions WHERE id=?', [$id]);
    json_response(['ok' => true]);
}
error_response('Not found', 404);
