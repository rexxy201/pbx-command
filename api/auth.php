<?php
require_once dirname(__DIR__) . '/src/config.php';
require_once dirname(__DIR__) . '/src/Database.php';
require_once dirname(__DIR__) . '/src/helpers.php';
require_once dirname(__DIR__) . '/src/auth.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'POST' && $action === 'login') {
    $body = get_json_body();
    $email = trim($body['email'] ?? '');
    $pass  = $body['password'] ?? '';
    if (!$email || !$pass) error_response('Email and password required');
    $user = login($email, $pass);
    if (!$user) error_response('Invalid email or password', 401);
    json_response(['ok' => true, 'user' => ['name' => $user['name'], 'role' => $user['role']]]);
}

if ($method === 'POST' && $action === 'logout') {
    logout();
    json_response(['ok' => true]);
}

error_response('Not found', 404);
