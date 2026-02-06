<?php

setAccessControl();
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Methods: GET, OPTIONS');
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

$pdo = databaseConnect();

$locationId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if($locationId) {
    $location = getLocation($pdo, $locationId, LocationSearchableFields::ID);

    if(!$location) {
        respond(404, ['error' => 'Location not found']);
    }

    if($location['user_id'] !== $userId) {
        respond(403, ['error' => 'Access denied']);
    }

    respond(200, $location);
}

$locations = getLocationsByUserId($pdo, $userId);
respond(200, ['locations' => $locations]);
