<?php
/**
 * Legacy FreePBX settings endpoint — also mirrors to system_settings for unified access.
 */
require_once dirname(__DIR__) . '/src/config.php';
require_once dirname(__DIR__) . '/src/Database.php';
require_once dirname(__DIR__) . '/src/helpers.php';
require_once dirname(__DIR__) . '/src/auth.php';
require_once dirname(__DIR__) . '/src/Settings.php';

require_auth_api();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $row = Database::row('SELECT id, url, api_key, ami_host, ami_port, ami_username FROM freepbx_settings LIMIT 1');
    json_response($row ?? (object)[]);
}

if ($method === 'POST') {
    $b = get_json_body();
    $existing = Database::row('SELECT id FROM freepbx_settings LIMIT 1');

    if ($existing) {
        $params = [$b['url'] ?? null, $b['apiKey'] ?? null, $b['amiHost'] ?? null, (int)($b['amiPort'] ?? 5038), $b['amiUsername'] ?? null];
        $sets = ['url=?', 'api_key=?', 'ami_host=?', 'ami_port=?', 'ami_username=?', 'updated_at=NOW()'];
        if (!empty($b['apiSecret'])) { $sets[] = 'api_secret=?'; $params[] = $b['apiSecret']; }
        if (!empty($b['amiPassword'])) { $sets[] = 'ami_password=?'; $params[] = $b['amiPassword']; }
        $params[] = $existing['id'];
        Database::execute('UPDATE freepbx_settings SET ' . implode(',', $sets) . ' WHERE id=?', $params);
    } else {
        Database::insert(
            'INSERT INTO freepbx_settings (url, api_key, api_secret, ami_host, ami_port, ami_username, ami_password) VALUES (?,?,?,?,?,?,?)',
            [$b['url'] ?? null, $b['apiKey'] ?? null, $b['apiSecret'] ?? null, $b['amiHost'] ?? null, (int)($b['amiPort'] ?? 5038), $b['amiUsername'] ?? null, $b['amiPassword'] ?? null]
        );
    }

    // Mirror to system_settings for unified access
    $kvF = ['freepbx.url' => $b['url'] ?? '', 'freepbx.api_key' => $b['apiKey'] ?? ''];
    if (!empty($b['apiSecret'])) $kvF['freepbx.api_secret'] = $b['apiSecret'];
    Settings::setGroup('freepbx', $kvF);

    $kvA = ['ami.host' => $b['amiHost'] ?? '', 'ami.port' => (string)($b['amiPort'] ?? '5038'), 'ami.username' => $b['amiUsername'] ?? ''];
    if (!empty($b['amiPassword'])) $kvA['ami.password'] = $b['amiPassword'];
    Settings::setGroup('ami', $kvA);

    json_response(['ok' => true]);
}

error_response('Not found', 404);
