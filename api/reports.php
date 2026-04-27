<?php
require_once dirname(__DIR__) . '/src/config.php';
require_once dirname(__DIR__) . '/src/Database.php';
require_once dirname(__DIR__) . '/src/helpers.php';
require_once dirname(__DIR__) . '/src/auth.php';

require_auth_api();

$from   = $_GET['from']   ?? date('Y-m-d', strtotime('-7 days'));
$to     = $_GET['to']     ?? date('Y-m-d');
$export = $_GET['export'] ?? '';
$fromDt = $from . ' 00:00:00';
$toDt   = $to   . ' 23:59:59';

if ($export === 'csv') {
    $agents = Database::query(
        "SELECT agent_name AS name,
                COUNT(*) AS total,
                SUM(CASE WHEN status='answered' THEN 1 ELSE 0 END) AS answered,
                SUM(CASE WHEN status='missed'   THEN 1 ELSE 0 END) AS missed,
                ROUND(COALESCE(AVG(CASE WHEN status='answered' AND duration>0 THEN duration END), 0)) AS avg_duration
         FROM call_logs
         WHERE agent_name IS NOT NULL AND created_at BETWEEN ? AND ?
         GROUP BY agent_name ORDER BY total DESC", [$fromDt, $toDt]
    );

    $filename = 'agent-performance-' . $from . '-to-' . $to . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, no-store');
    header('Pragma: no-cache');

    function csv_safe(string $val): string {
        return preg_match('/^[=+\-@\t\r]/', $val) ? "'" . $val : $val;
    }

    $out = fopen('php://output', 'w');
    fputcsv($out, ['Agent', 'Total', 'Answered', 'Missed', 'Answer Rate (%)', 'Avg Duration (sec)']);
    foreach ($agents as $a) {
        $rate = $a['total'] > 0 ? round($a['answered'] / $a['total'] * 100) : 0;
        fputcsv($out, [
            csv_safe($a['name']),
            (int)$a['total'],
            (int)$a['answered'],
            (int)$a['missed'],
            $rate,
            (int)$a['avg_duration'],
        ]);
    }
    fclose($out);
    exit;
}

$summary = Database::row(
    "SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN status='answered' THEN 1 ELSE 0 END) AS answered,
        SUM(CASE WHEN status='missed'   THEN 1 ELSE 0 END) AS missed,
        ROUND(COALESCE(AVG(CASE WHEN status='answered' AND duration>0 THEN duration END), 0)) AS avg_duration
     FROM call_logs WHERE created_at BETWEEN ? AND ?", [$fromDt, $toDt]
);

$trend = Database::query(
    "SELECT DATE(created_at) AS day,
            COUNT(*) AS total,
            SUM(CASE WHEN status='answered' THEN 1 ELSE 0 END) AS answered
     FROM call_logs WHERE created_at BETWEEN ? AND ?
     GROUP BY DATE(created_at) ORDER BY day", [$fromDt, $toDt]
);

$agents = Database::query(
    "SELECT agent_name AS name,
            COUNT(*) AS total,
            SUM(CASE WHEN status='answered' THEN 1 ELSE 0 END) AS answered,
            SUM(CASE WHEN status='missed'   THEN 1 ELSE 0 END) AS missed,
            ROUND(COALESCE(AVG(CASE WHEN status='answered' AND duration>0 THEN duration END), 0)) AS avg_duration
     FROM call_logs
     WHERE agent_name IS NOT NULL AND created_at BETWEEN ? AND ?
     GROUP BY agent_name ORDER BY total DESC", [$fromDt, $toDt]
);

$ivr = Database::query(
    "SELECT ivr_path AS label, COUNT(*) AS count
     FROM call_logs WHERE ivr_path IS NOT NULL AND created_at BETWEEN ? AND ?
     GROUP BY ivr_path ORDER BY count DESC LIMIT 10", [$fromDt, $toDt]
);

json_response(['summary'=>$summary,'trend'=>$trend,'agents'=>$agents,'ivr'=>$ivr]);
