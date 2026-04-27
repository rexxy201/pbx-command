<?php
/**
 * Database configuration.
 *
 * FOR cPANEL HOSTING: Fill in your MySQL credentials below.
 * FOR Replit dev: The DATABASE_URL environment variable is used automatically.
 */

$dbUrl = getenv('DATABASE_URL');

if ($dbUrl && (str_starts_with($dbUrl, 'postgres') || str_starts_with($dbUrl, 'pgsql'))) {
    // Replit development (PostgreSQL)
    $parts = parse_url($dbUrl);
    define('DB_DRIVER', 'pgsql');
    define('DB_HOST',   $parts['host'] ?? 'localhost');
    define('DB_PORT',   $parts['port'] ?? 5432);
    define('DB_NAME',   ltrim($parts['path'] ?? '/pbx', '/'));
    define('DB_USER',   $parts['user'] ?? 'postgres');
    define('DB_PASS',   urldecode($parts['pass'] ?? ''));

} elseif ($dbUrl && str_starts_with($dbUrl, 'mysql')) {
    // MySQL via environment variable
    $parts = parse_url($dbUrl);
    define('DB_DRIVER', 'mysql');
    define('DB_HOST',   $parts['host'] ?? 'localhost');
    define('DB_PORT',   $parts['port'] ?? 3306);
    define('DB_NAME',   ltrim($parts['path'] ?? '/pbx', '/'));
    define('DB_USER',   $parts['user'] ?? 'root');
    define('DB_PASS',   urldecode($parts['pass'] ?? ''));

} else {
    // ── cPanel MySQL — edit these credentials ────────────────────────────────
    define('DB_DRIVER', 'mysql');
    define('DB_HOST', 'localhost');
    define('DB_PORT', 3306);
    define('DB_NAME', 'pbx_dashboard');   // your cPanel database name
    define('DB_USER', 'pbx_user');        // your cPanel MySQL username
    define('DB_PASS', 'your_password');   // your cPanel MySQL password
    // ─────────────────────────────────────────────────────────────────────────
}

define('APP_NAME',    'PBX Command');
define('APP_VERSION', '2.0.0');
define('SESSION_TTL', 60 * 60 * 24 * 7); // 7 days

$basePath = getenv('BASE_PATH') ?: '';
define('BASE_PATH', rtrim($basePath, '/'));
