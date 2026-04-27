<?php
declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/src/config.php';
require_once APP_ROOT . '/src/Database.php';
require_once APP_ROOT . '/src/helpers.php';
require_once APP_ROOT . '/src/auth.php';
require_once APP_ROOT . '/src/layout.php';
require_once APP_ROOT . '/src/Settings.php';
require_once APP_ROOT . '/src/bootstrap.php';

// ── Static assets: serve directly ────────────────────────────────────────────
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Strip base path from URI
if (BASE_PATH !== '' && str_starts_with($uri, BASE_PATH)) {
    $uri = substr($uri, strlen(BASE_PATH));
}
if ($uri === '' || $uri === null) $uri = '/';

// Serve static files (CSS, JS, images)
$staticFile = __DIR__ . $uri;
if ($uri !== '/' && file_exists($staticFile) && !is_dir($staticFile)) {
    $ext = strtolower(pathinfo($staticFile, PATHINFO_EXTENSION));
    $mime = match($ext) {
        'css'  => 'text/css',
        'js'   => 'application/javascript',
        'png'  => 'image/png',
        'jpg','jpeg' => 'image/jpeg',
        'svg'  => 'image/svg+xml',
        'ico'  => 'image/x-icon',
        'woff2'=> 'font/woff2',
        default => 'application/octet-stream',
    };
    header("Content-Type: $mime");
    readfile($staticFile);
    exit;
}

// ── API Routes ────────────────────────────────────────────────────────────────
if (str_starts_with($uri, '/pbx-api/')) {
    $endpoint = ltrim(substr($uri, 9), '/');
    // Strip query string
    $endpoint = strtok($endpoint, '?') ?: $endpoint;
    $apiFile = APP_ROOT . '/api/' . $endpoint . '.php';

    if (file_exists($apiFile)) {
        require $apiFile;
        exit;
    }
    error_response('API endpoint not found', 404);
}

// ── Auth routes ───────────────────────────────────────────────────────────────
if ($uri === '/login' || $uri === '/login/') {
    require APP_ROOT . '/src/pages/login.php';
    exit;
}

if ($uri === '/logout' || $uri === '/logout/') {
    logout();
    header('Location: ' . BASE_PATH . '/login');
    exit;
}

// ── All other routes require auth ─────────────────────────────────────────────
$user = require_auth();

// Page routing map
$routes = [
    '/'                   => 'dashboard',
    '/dashboard'          => 'dashboard',
    '/live-monitor'       => 'live-monitor',
    '/extensions'         => 'extensions',
    '/call-queues'        => 'call-queues',
    '/ring-groups'        => 'ring-groups',
    '/ivr-menus'          => 'ivr-menus',
    '/sla-rules'          => 'sla-rules',
    '/time-conditions'    => 'time-conditions',
    '/call-logs'          => 'call-logs',
    '/reports'            => 'reports',
    '/sip-trunks'         => 'sip-trunks',
    '/pbx-agents'         => 'pbx-agents',
    '/dashboard-users'    => 'dashboard-users',
    '/whatsapp'           => 'whatsapp',
    '/settings'           => 'freepbx-settings',
];

$page = $routes[$uri] ?? $routes[rtrim($uri, '/')] ?? null;

if (!$page) {
    http_response_code(404);
    echo '<!DOCTYPE html><html><body style="background:#0a0d14;color:#e2e8f0;font-family:sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;margin:0"><div style="text-align:center"><h1 style="font-size:48px;color:#38bdf8">404</h1><p>Page not found</p><a href="' . BASE_PATH . '/" style="color:#38bdf8">Go home</a></div></body></html>';
    exit;
}

$pageFile = APP_ROOT . '/src/pages/' . $page . '.php';
require $pageFile;
