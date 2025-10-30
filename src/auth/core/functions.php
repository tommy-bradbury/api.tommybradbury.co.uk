<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

/**
 * Initial Headers
 *
 * @return void
 */
function setAccessControl(): void
{
    header('Vary: Origin');
    header('Access-Control-Allow-Origin: https://tommybradbury.co.uk');
    header('Access-Control-Allow-Credentials: true');
}

/**
 * API Generate response
 *
 * @param int $httpResponseCode
 * @param array $response
 * @return never
 */
function respond(int $httpResponseCode, array $response): never
{
    http_response_code($httpResponseCode);
    echo json_encode($response);
    exit;
}

/**
 * Get PDO connection
 *
 * @return PDO
 */
function databaseConnect(): PDO
{
    $dbHost = getenv('DB_HOST');
    $dbName = getenv('DB_NAME');
    $dbUser = getenv('DB_USER');
    $dbPass = getenv('DB_PASS');
    try {
        $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4", $dbUser, $dbPass,  [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    } catch(Throwable $e) {
        respond(503, ['error' => 'Service unavailable: ' . $e->getMessage()]);
    }
    return $pdo;
}

/**
 * Search user by Email
 *
 * @param PDO $pdo
 * @param string $email
 * @return array|false
 */
function getUserByEmail(PDO $pdo, string $email): array|false
{
    $stmt = $pdo->prepare('SELECT id, password_hash FROM users WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    return $user;
}

/**
 * Validate  session cookie and return token
 * @param string $secret
 * @return array decoded JWT payload
 * @throws Exception If validation fails, the function directly responds.
 */
function validateJwtCookie(string $secret): array
{
    $jwt = $_COOKIE['session'] ?? null;
    if(!$jwt) {
        respond(401, ['error' => 'No session token']);
    }

    try {
        $key = new Key($secret, 'HS256');
        $decoded = JWT::decode($jwt, $key);
        return (array)$decoded;
    } catch(ExpiredException $e) {
        respond(401, ['error' => 'Session expired']);
    } catch(SignatureInvalidException $e) {
        respond(401, ['error' => 'Invalid token signature']);
    } catch(\Throwable $e) {
        respond(401, ['error' => 'Invalid session token']);
    }
}

/**
 * @param array $oldPayload
 * @param string $secret
 * @param int $durationSeconds
 * @return void
 * @throws \Random\RandomException
 */
function refreshJwtCookie(array $oldPayload, string $secret, int $durationSeconds = 3600): void
{
    $sub = $oldPayload['sub'] ?? null;
    $iss = $oldPayload['iss'] ?? 'https://api.tommybradbury.co.uk';
    $aud = $oldPayload['aud'] ?? 'https://tommybradbury.co.uk';

    if (empty($sub)) {
        return;
    }

    $now = time();
    $exp = $now + $durationSeconds;
    $jti = bin2hex(random_bytes(16)); // Crucial: New unique token ID

    // Create the new JWT payload
    $newPayload = [
        'iss' => $iss,
        'aud' => $aud,
        'iat' => $now,
        'nbf' => $now,
        'exp' => $exp,
        'sub' => $sub,
        'jti' => $jti
    ];

    $newJwt = JWT::encode($newPayload, $secret, 'HS256');
    setcookie('session', $newJwt, ['expires' => $exp, 'path' => '/', 'domain' => '.tommybradbury.co.uk', 'secure' => true, 'httponly' => true, 'samesite' => 'Strict']);

}
