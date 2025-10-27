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

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
$email = isset($data['email']) ? filter_var($data['email'], FILTER_VALIDATE_EMAIL) : null;
$password = isset($data['password']) ? (string)$data['password'] : null;
if(!isset($email, $password) || $email === false)
{
    respond(400, ['error' => 'Invalid request']);
}

$dbHost = getenv('DB_HOST');
$dbName = getenv('DB_NAME');
$dbUser = getenv('DB_USER');
$dbPass = getenv('DB_PASS');
try {
    $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4", $dbUser, $dbPass,  [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
} catch(Throwable $e) {
    respond(503, ['error' => 'Service unavailable: ' . $e->getMessage()]);
}

$stmt = $pdo->prepare('SELECT id, password_hash FROM users WHERE email = :email LIMIT 1');
$stmt->execute([':email' => $email]);
$user = $stmt->fetch();

// normalize timing if user not found
$hash = $user['password_hash'] ?? '$2y$10$hXr5XW.gm9e7QkX//0VnweSpFNR2N3lMZQ6wX2gHsxlQkTTAQvjdq';
$isValid = password_verify($password, $hash);
if(!$isValid) {
    respond(401, ['error' => 'Invalid credentials']);
}

if($user && password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
    $newHash = password_hash($password, PASSWORD_DEFAULT);
    $reh = $pdo->prepare('UPDATE users SET password_hash = :h WHERE id = :id');
    try {
        $reh->execute([':h' => $newHash, ':id' => $user['id']]);
    } catch (Throwable $e) {
        // no action
    }
}

// JWT with 1 hour timeout
$now = time();
$exp = $now + 3600;
$jti = bin2hex(random_bytes(16));
$jwtPayload = ['iss' => 'https://api.example.co.uk', 'aud' => 'https://example.co.uk', 'iat' => $now, 'nbf' => $now, 'exp' => $exp, 'sub' => (string)$user['id'], 'jti' => $jti];
$secret = getenv('APP_JWT_SECRET');

if (!$secret) {
    respond(500, ['error' => 'Server misconfiguration']);
}
$jwt = JWT::encode($jwtPayload, $secret, 'HS256');

setcookie('session', $jwt, ['expires' => $exp, 'path' => '/', 'domain' => '.tommybradbury.co.uk', 'secure' => true, 'httponly' => true, 'samesite' => 'Strict']);
respond(200, []);
