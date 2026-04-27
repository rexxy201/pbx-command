<?php
require_once dirname(__DIR__) . '/src/config.php';
require_once dirname(__DIR__) . '/src/Database.php';
require_once dirname(__DIR__) . '/src/helpers.php';
require_once dirname(__DIR__) . '/src/auth.php';

require_auth_api();

// Try the Node.js API server's active calls endpoint for live data
$nodeApi = getenv('NODE_API_URL') ?: 'http://localhost:' . (getenv('API_PORT') ?: '3001');
$response = @file_get_contents($nodeApi . '/api/active-calls', false, stream_context_create([
    'http' => ['timeout' => 2, 'header' => 'Accept: application/json']
]));

if ($response !== false) {
    header('Content-Type: application/json');
    echo $response;
    exit;
}

// Fallback: return empty active calls
json_response(['activeCalls' => [], 'queueStats' => []]);
