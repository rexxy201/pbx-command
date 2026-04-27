<?php
function formatDuration(int $seconds): string {
    if ($seconds <= 0) return '-';
    $m = intdiv($seconds, 60);
    $s = $seconds % 60;
    if ($m === 0) return "{$s}s";
    return "{$m}m {$s}s";
}

function formatDateTime(string $dt): string {
    return date('M j, H:i:s', strtotime($dt));
}

function formatDate(string $dt): string {
    return date('M j, Y', strtotime($dt));
}

function statusBadge(string $status): string {
    $map = [
        'answered'     => 'bg-success',
        'active'       => 'bg-success',
        'registered'   => 'bg-success',
        'missed'       => 'bg-danger',
        'unregistered' => 'bg-danger',
        'inactive'     => 'bg-secondary',
        'voicemail'    => 'bg-secondary',
        'transferred'  => 'bg-info text-dark',
        'busy'         => 'bg-warning text-dark',
    ];
    $cls = $map[$status] ?? 'bg-secondary';
    return "<span class=\"badge $cls\">" . htmlspecialchars($status) . "</span>";
}

function roleBadge(string $role): string {
    $map = [
        'admin'    => 'bg-primary',
        'manager'  => 'bg-info text-dark',
        'employee' => 'bg-success',
    ];
    $cls = $map[$role] ?? 'bg-secondary';
    return "<span class=\"badge $cls\">" . htmlspecialchars(ucfirst($role)) . "</span>";
}

function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function json_response(mixed $data, int $code = 200): never {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function error_response(string $message, int $code = 400): never {
    json_response(['error' => $message], $code);
}

function get_json_body(): array {
    $raw = file_get_contents('php://input');
    return json_decode($raw, true) ?? [];
}
