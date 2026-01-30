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
    $allowedOrigin = getenv('ENVIRONMENT') === 'development' ? 'http://localhost:3000' : 'somethingelse';
    header("Access-Control-Allow-Origin: {$allowedOrigin}");
    header('Vary: Origin');
    header('Access-Control-Allow-Credentials: true');
}

/**
 * Parses POST parameters either as raw application/json or
 * application/x-www-form-urlencoded and multipart/form-data
 *
 * @return array
 */
function parsePOSTParameters(): array
{
    $contentType = $_SERVER["CONTENT_TYPE"] ?? '';

    if (stripos($contentType, 'application/json') !== false) {
        $rawInput = file_get_contents("php://input");
        $decoded = json_decode($rawInput, true);
        return is_array($decoded) ? $decoded : [];
    }

    return $_POST;
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
    if(!empty($response)) {
        echo json_encode($response);
    }
    exit;
}

/**
 * Get PDO connection
 *
 * @return PDO
 */
function databaseConnect(): PDO
{
    $dbHost = $_ENV['DB_HOST'];
    $dbName = $_ENV['DB_NAME'];
    $dbUser = $_ENV['DB_USER'];
    $dbPass = $_ENV['DB_PASS'];
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
function getUser(PDO $pdo, string $searchValue, UserSearchableFields $field): array|false
{
    $stmt = false;
    switch($field)
    {
        case UserSearchableFields::EMAIL:
            $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
            $stmt->execute([':email' => $searchValue]);
            break;
        case UserSearchableFields::ID:
            $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id');
            $stmt->execute([':email' => $searchValue]);
            break;
    }

    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    return $user;
}

/**
 * Validate  session cookie and return token
 * @param string $secret
 * @return array decoded JWT payload
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

/**
 * Get location by ID or User ID
 *
 * @param PDO $pdo
 * @param string|int $searchValue
 * @param LocationSearchableFields $field
 * @return array|false
 */
function getLocation(PDO $pdo, string|int $searchValue, LocationSearchableFields $field): array|false
{
    $stmt = false;
    switch($field)
    {
        case LocationSearchableFields::ID:
            $stmt = $pdo->prepare('SELECT * FROM locations WHERE id = :id AND deleted_at IS NULL');
            $stmt->execute([':id' => $searchValue]);
            break;
        case LocationSearchableFields::USER_ID:
            $stmt = $pdo->prepare('SELECT * FROM locations WHERE user_id = :user_id AND deleted_at IS NULL');
            $stmt->execute([':user_id' => $searchValue]);
            break;
    }

    $location = $stmt->fetch(PDO::FETCH_ASSOC);
    return $location;
}

/**
 * Get all locations for a user
 *
 * @param PDO $pdo
 * @param int $userId
 * @return array
 */
function getLocationsByUserId(PDO $pdo, int $userId): array
{
    $stmt = $pdo->prepare('SELECT * FROM locations WHERE user_id = :user_id AND deleted_at IS NULL');
    $stmt->execute([':user_id' => $userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}