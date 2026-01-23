<?php

use Firebase\JWT\JWT;

setAccessControl();
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    http_response_code(204);
    exit;
}
header('Content-Type: application/json; charset=utf-8');


$req = parsePOSTParameters();
$email = isset($req['email']) ? filter_var($req['email'], FILTER_VALIDATE_EMAIL) : null;
$password = isset($req['password']) ? (string)$req['password'] : null;


if(!isset($email, $password) || $email === false || strlen($password) < 8) {
    respond(400, ['error' => 'Invalid signup data']);
}

$pdo = databaseConnect();
if(getUser($pdo, $email, UserSearchableFields::EMAIL)) {
    respond(409, ['error' => 'User already exists']);
}

$password_hash = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare('INSERT INTO users (email, password_hash) VALUES (:email, :hash)');
    $stmt->execute([':email' => $email, ':hash' => $password_hash]);
    $user_id = $pdo->lastInsertId();
} catch(Throwable $e) {
    respond(500, ['error' => 'Could not create user, try again later']);
}

// JWT with 1 hour timeout
$now = time();
$exp = $now + 3600;
$jti = bin2hex(random_bytes(16));
$jwtPayload = [
    'iss' => 'https://api.tommybradbury.co.uk',
    'aud' => 'https://tommybradbury.co.uk',
    'iat' => $now,
    'nbf' => $now,
    'exp' => $exp,
    'sub' => (string)$user_id,
    'jti' => $jti
];

$secret = $_ENV['APP_JWT_SECRET'];
if(!$secret) {
    respond(500, ['error' => 'Server misconfiguration']);
}
$jwt = JWT::encode($jwtPayload, $secret, 'HS256');


if(headers_sent($file, $line)) {
    die("Cannot set cookie: headers sent in $file:$line");
}

if(getenv('ENVIRONMENT') === 'development') {
    setcookie(
        'session',
        $jwt,
        [
            'expires' => $exp,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax'
        ]
    );
} else {
    setcookie(
        'session',
        $jwt,
        [
            'expires' => $exp,
            'path' => '/',
            'domain' => '.tommybradbury.co.uk',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Strict'
        ]
    );
}

respond(201, []);
