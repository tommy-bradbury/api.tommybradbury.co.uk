<?php
use Firebase\JWT\JWT;

setAccessControl();
if($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    http_response_code(204);
    exit;
}
header('Content-Type: application/json;');
header('charset=utf-8');

$raw        = file_get_contents('php://input');
$data       = json_decode($raw, true);
$email      = isset($data['email']) ? filter_var($data['email'], FILTER_VALIDATE_EMAIL) : null;
$password   = isset($data['password']) ? (string)$data['password'] : null;
if(!isset($email, $password) || $email === false) {
    respond(400, ['error' => 'Invalid request']);
}

$pdo        = databaseConnect();
$user       = getUserByEmail($pdo, $email);
$hash       = $user['password_hash'] ?? '$2y$10$hXr5XW.gm9e7QkX//0VnweSpFNR2N3lMZQ6wX2gHsxlQkTTAQvjdq';
$isValid    = password_verify($password, $hash);
if(!$isValid) {
    respond(401, ['error' => 'Invalid credentials']);
}

if($user && password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
    $newHash    = password_hash($password, PASSWORD_DEFAULT);
    $rehash     = $pdo->prepare('UPDATE users SET password_hash = :h WHERE id = :id');
    try {
        $rehash->execute([':h' => $newHash, ':id' => $user['id']]);
    } catch (Throwable $e) {
        // no action
    }
}

// JWT with 1 hour timeout
$now = time();
$exp = $now + 3600;
$jti = bin2hex(random_bytes(16));
$jwtPayload = ['iss' => 'https://api.tommybradbury.co.uk', 'aud' => 'https://tommybradbury.co.uk', 'iat' => $now, 'nbf' => $now, 'exp' => $exp, 'sub' => (string)$user['id'], 'jti' => $jti];
$secret = getenv('APP_JWT_SECRET');

if(!$secret) {
    respond(500, ['error' => 'Server misconfiguration']);
}
$jwt = JWT::encode($jwtPayload, $secret, 'HS256');

setcookie('session', $jwt, ['expires' => $exp, 'path' => '/', 'domain' => '.tommybradbury.co.uk', 'secure' => true, 'httponly' => true, 'samesite' => 'Strict']);
respond(200, []);
