<?php
require_once dirname(__DIR__) . '/src/config.php';
require_once dirname(__DIR__) . '/src/Database.php';
require_once dirname(__DIR__) . '/src/helpers.php';
require_once dirname(__DIR__) . '/src/auth.php';

require_auth_api();

$search = $_GET['search'] ?? '';
$export = $_GET['export'] ?? '';
$limit  = min((int)($_GET['limit'] ?? 50), 200);
$offset = (int)($_GET['offset'] ?? 0);

$where = '1=1';
$params = [];
if ($search) {
    $where .= ' AND (caller_number LIKE ? OR caller_name LIKE ? OR destination LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($export === 'csv') {
    $rows = Database::query(
        "SELECT created_at, caller_number, caller_name, destination, ivr_path, status, duration
         FROM call_logs
         WHERE $where
         ORDER BY created_at DESC",
        $params
    );

    $filename = 'call-logs-' . date('Y-m-d') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, no-store');
    header('Pragma: no-cache');

    function csv_safe(string $val): string {
        return preg_match('/^[=+\-@\t\r]/', $val) ? "'" . $val : $val;
    }

    $out = fopen('php://output', 'w');
    fputcsv($out, ['Time', 'Caller Number', 'Caller Name', 'Destination', 'IVR Path', 'Status', 'Duration (sec)']);
    foreach ($rows as $r) {
        fputcsv($out, [
            $r['created_at'],
            csv_safe($r['caller_number'] ?? ''),
            csv_safe($r['caller_name'] ?? ''),
            csv_safe($r['destination'] ?? ''),
            csv_safe($r['ivr_path'] ?? ''),
            csv_safe($r['status'] ?? ''),
            (int)($r['duration'] ?? 0),
        ]);
    }
    fclose($out);
    exit;
}

$logs = Database::query(
    "SELECT id, caller_number, caller_name, destination, status, duration, ivr_path, created_at
     FROM call_logs
     WHERE $where
     ORDER BY created_at DESC
     LIMIT ? OFFSET ?",
    [...$params, $limit, $offset]
);

$count = Database::row("SELECT COUNT(*) AS cnt FROM call_logs WHERE $where", $params);

json_response(['logs' => $logs, 'total' => (int)($count['cnt'] ?? 0)]);
