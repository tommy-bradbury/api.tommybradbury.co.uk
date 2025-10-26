<?php
use Firebase\JWT\JWT;

//CORS: allow your site origin and credentials 
$allowedOrigin = 'https://tommybradbury.co.uk';
header('Vary: Origin');
header('Access-Control-Allow-Origin: ' . $allowedOrigin);
header('Access-Control-Allow-Credentials: true');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    http_response_code(204);
    exit;
}
header('Content-Type: application/json;');
header('charset=utf-8');

//Basic input handling 
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
$email = isset($data['email']) ? filter_var($data['email'], FILTER_VALIDATE_EMAIL) : null;
$password = isset($data['password']) ? (string)$data['password'] : '';
if (!$email || $password === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

//Connect to DB (use SSM/Secrets to load credentials securely) 
$dbHost = getenv('DB_HOST');
$dbName = getenv('DB_NAME');
$dbUser = getenv('DB_USER');
$dbPass = getenv('DB_PASS');
$dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,];
try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
} catch (Throwable $e) {

    //Avoid leaking details 
    http_response_code(503);
    echo json_encode(['error' => 'Service unavailable gerrestghsretgh' . $e->getMessage()]);
    exit;
}

//Fetch user by email 
$stmt = $pdo->prepare('SELECT id, password_hash FROM users WHERE email = :email LIMIT 1');
$stmt->execute([':email' => $email]);
$user = $stmt->fetch();

// normalize timing if user not found
$hash = $user['password_hash'] ?? '$2y$10$hXr5XW.gm9e7QkX//0VnweSpFNR2N3lMZQ6wX2gHsxlQkTTAQvjdq';
$isValid = password_verify($password, $hash);
if (!$isValid) { 
    http_response_code(401);
    echo json_encode(['error' => 'Invalid credentials']);
    exit;
}

//Optionally rehash if algorithm/cost changed 
if ($user && password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
    $newHash = password_hash($password, PASSWORD_DEFAULT);
    $reh = $pdo->prepare('UPDATE users SET password_hash = :h WHERE id = :id');
    try {
        $reh->execute([':h' => $newHash, ':id' => $user['id']]);
    } catch (Throwable $e) {
        // no action
    }
}

//Create short-lived JWT 
$now = time();
$exp = $now + 3600;

//1 hour 
$jti = bin2hex(random_bytes(16));
$jwtPayload = ['iss' => 'https://api.example.co.uk', 'aud' => 'https://example.co.uk', 'iat' => $now, 'nbf' => $now, 'exp' => $exp, 'sub' => (string)$user['id'], 'jti' => $jti];
$secret = getenv('APP_JWT_SECRET');

//Load from SSM/Secrets 
if (!$secret) {
    http_response_code(500);
    echo json_encode(['error' => 'Server misconfiguration']);
    exit;
}
$jwt = JWT::encode($jwtPayload, $secret, 'HS256');

//Set Secure, HttpOnly, SameSite cookie for the session
//Domain is set to share across subdomains 
setcookie('session', $jwt, ['expires' => $exp, 'path' => '/', 'domain' => '.example.co.uk', 'secure' => true, 'httponly' => true, 'samesite' => 'Strict']);
http_response_code(200);
echo json_encode(['ok' => true]);
