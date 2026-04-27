<?php
require_once dirname(__DIR__) . '/src/config.php';
require_once dirname(__DIR__) . '/src/Database.php';
require_once dirname(__DIR__) . '/src/helpers.php';
require_once dirname(__DIR__) . '/src/auth.php';

require_auth_api();
$method = $_SERVER['REQUEST_METHOD'];
$id     = (int)($_GET['id'] ?? 0);

if ($method === 'GET') {
    json_response(Database::query('SELECT * FROM sla_rules ORDER BY name'));
}
if ($method === 'POST') {
    $b = get_json_body();
    if (empty($b['name'])) error_response('name required');
    $newId = Database::insert(
        'INSERT INTO sla_rules (name, target_answer_time, target_abandon_rate, threshold_warning, threshold_critical, is_active, notes) VALUES (?,?,?,?,?,?,?)',
        [$b['name'], (int)($b['targetAnswerTime'] ?? 20), (float)($b['targetAbandonRate'] ?? 5.0), (int)($b['thresholdWarning'] ?? 80), (int)($b['thresholdCritical'] ?? 70), true, $b['notes'] ?? null]
    );
    json_response(Database::row('SELECT * FROM sla_rules WHERE id=?', [$newId]), 201);
}
if ($method === 'PUT' && $id) {
    $b = get_json_body();
    Database::execute(
        'UPDATE sla_rules SET name=?, target_answer_time=?, target_abandon_rate=?, threshold_warning=?, threshold_critical=?, is_active=?, notes=?, updated_at=NOW() WHERE id=?',
        [$b['name'], (int)($b['targetAnswerTime'] ?? 20), (float)($b['targetAbandonRate'] ?? 5.0), (int)($b['thresholdWarning'] ?? 80), (int)($b['thresholdCritical'] ?? 70), (bool)($b['isActive'] ?? true), $b['notes'] ?? null, $id]
    );
    json_response(Database::row('SELECT * FROM sla_rules WHERE id=?', [$id]));
}
if ($method === 'DELETE' && $id) {
    Database::execute('DELETE FROM sla_rules WHERE id=?', [$id]);
    json_response(['ok' => true]);
}
error_response('Not found', 404);
