<?php
require_once __DIR__ . '/Database.php';

function session_start_secure(): void {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.cookie_httponly', '1');
        ini_set('session.use_strict_mode', '1');
        session_start();
    }
}

function current_user(): ?array {
    session_start_secure();
    if (!isset($_SESSION['user_id'])) return null;
    // Refresh from DB each request for up-to-date role/status
    $user = Database::row(
        'SELECT id, name, email, role, status FROM dashboard_users WHERE id = ? AND status = \'active\'',
        [$_SESSION['user_id']]
    );
    if (!$user) {
        session_destroy();
        return null;
    }
    return $user;
}

function require_auth(): array {
    $user = current_user();
    if (!$user) {
        header('Location: ' . BASE_PATH . '/login');
        exit;
    }
    return $user;
}

function require_auth_api(): array {
    session_start_secure();
    $user = current_user();
    if (!$user) {
        error_response('Unauthorized', 401);
    }
    return $user;
}

function login(string $email, string $pass): ?array {
    $user = Database::row(
        'SELECT * FROM dashboard_users WHERE email = ? AND status = \'active\'',
        [strtolower(trim($email))]
    );
    if (!$user || empty($user['password_hash'])) return null;
    if (!password_verify($pass, $user['password_hash'])) return null;

    session_start_secure();
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];

    // Update last_login_at
    Database::execute(
        'UPDATE dashboard_users SET last_login_at = NOW() WHERE id = ?',
        [$user['id']]
    );

    return $user;
}

function logout(): void {
    session_start_secure();
    session_destroy();
}
