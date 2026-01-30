<?php

setAccessControl();
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Methods: DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    http_response_code(204);
    exit;
}
header('Content-Type: application/json; charset=utf-8');

$secret = $_ENV['APP_JWT_SECRET'];
if(!$secret) {
    respond(500, ['error' => 'Server misconfiguration']);
}

$payload = validateJwtCookie($secret);
$userId = (int)$payload['sub'];

$req = parsePOSTParameters();
$locationId = isset($req['id']) ? (int)$req['id'] : null;

if(!$locationId) {
    respond(400, ['error' => 'Location ID is required']);
}

$pdo = databaseConnect();

$location = getLocation($pdo, $locationId, LocationSearchableFields::ID);
if(!$location) {
    respond(404, ['error' => 'Location not found']);
}

if($location['user_id'] !== $userId) {
    respond(403, ['error' => 'Access denied']);
}

try {
    $success = deleteLocation($pdo, $locationId, $userId);
    if($success) {
        respond(200, ['success' => true]);
    } else {
        respond(404, ['error' => 'Location not found']);
    }
} catch(Throwable $e) {
    respond(500, ['error' => 'Could not delete location']);
}
