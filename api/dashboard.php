<?php
require_once dirname(__DIR__) . '/src/config.php';
require_once dirname(__DIR__) . '/src/Database.php';
require_once dirname(__DIR__) . '/src/helpers.php';
require_once dirname(__DIR__) . '/src/auth.php';

require_auth_api();

$range = (int)($_GET['range'] ?? 7);
$since = date('Y-m-d H:i:s', strtotime("-{$range} days"));

$totals = Database::row(
    "SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN status = 'answered' THEN 1 ELSE 0 END) AS answered,
        SUM(CASE WHEN status = 'missed'   THEN 1 ELSE 0 END) AS missed,
        ROUND(COALESCE(AVG(CASE WHEN status = 'answered' AND duration > 0 THEN duration END), 0)) AS avg_duration
     FROM call_logs WHERE created_at >= ?", [$since]
);

$activeExtensions = Database::row("SELECT COUNT(*) AS cnt FROM extensions WHERE status = 'registered'", []);
$totalExtensions  = Database::row("SELECT COUNT(*) AS cnt FROM extensions", []);

$agentStats = Database::query(
    "SELECT
        cl.agent_name AS name,
        COUNT(*) AS total,
        SUM(CASE WHEN cl.status = 'answered' THEN 1 ELSE 0 END) AS answered,
        SUM(CASE WHEN cl.status = 'missed'   THEN 1 ELSE 0 END) AS missed,
        ROUND(COALESCE(AVG(CASE WHEN cl.status = 'answered' AND cl.duration > 0 THEN cl.duration END), 0)) AS avg_duration
     FROM call_logs cl
     WHERE cl.agent_name IS NOT NULL AND cl.created_at >= ?
     GROUP BY cl.agent_name
     ORDER BY total DESC
     LIMIT 10", [$since]
);

$trend = Database::query(
    "SELECT
        DATE(created_at) AS day,
        COUNT(*) AS total,
        SUM(CASE WHEN status = 'answered' THEN 1 ELSE 0 END) AS answered
     FROM call_logs
     WHERE created_at >= ?
     GROUP BY DATE(created_at)
     ORDER BY day", [$since]
);

json_response([
    'totals'           => $totals,
    'activeExtensions' => (int)($activeExtensions['cnt'] ?? 0),
    'totalExtensions'  => (int)($totalExtensions['cnt'] ?? 0),
    'agentStats'       => $agentStats,
    'trend'            => $trend,
]);
