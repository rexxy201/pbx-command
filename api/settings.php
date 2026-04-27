<?php
require_once dirname(__DIR__) . '/src/config.php';
require_once dirname(__DIR__) . '/src/Database.php';
require_once dirname(__DIR__) . '/src/helpers.php';
require_once dirname(__DIR__) . '/src/auth.php';
require_once dirname(__DIR__) . '/src/Settings.php';

require_auth_api();

$method = $_SERVER['REQUEST_METHOD'];
$grp    = $_GET['group'] ?? '';

// Sensitive keys — never returned in GET responses
const SENSITIVE = [
    'freepbx.api_secret', 'ami.password',
    'whatsapp.access_token', 'smtp.password',
];

if ($method === 'GET') {
    if (!$grp) error_response('group param required');
    $data = Settings::group($grp);
    // Redact sensitive values
    foreach (SENSITIVE as $k) {
        if (isset($data[$k]) && $data[$k] !== '') {
            $data[$k] = '••••••••';
        }
    }
    json_response($data);
}

if ($method === 'POST') {
    $b = get_json_body();
    if (empty($b['group'])) error_response('group field required');
    $grp   = $b['group'];
    $pairs = $b['settings'] ?? [];

    if (!is_array($pairs) || empty($pairs)) error_response('settings array required');

    foreach ($pairs as $key => $val) {
        // Skip blank password fields so we don't overwrite existing values
        if (in_array($key, SENSITIVE) && ($val === '' || $val === '••••••••')) {
            continue;
        }
        Settings::set($key, $grp, (string)$val);
    }

    json_response(['ok' => true, 'group' => $grp]);
}

error_response('Method not allowed', 405);
