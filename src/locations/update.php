<?php

setAccessControl();
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Methods: PUT, OPTIONS');
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
$name = isset($req['name']) ? (string)$req['name'] : null;
$whatthreewords = isset($req['whatthreewords']) ? (string)$req['whatthreewords'] : null;
$latitude = isset($req['latitude']) ? (float)$req['latitude'] : null;
$longitude = isset($req['longitude']) ? (float)$req['longitude'] : null;

if(!$locationId) {
    respond(400, ['error' => 'Location ID is required']);
}

if(empty($whatthreewords) && (empty($latitude) || empty($longitude))) {
    respond(400, ['error' => 'Either whatthreewords or coordinates (latitude and longitude) must be provided']);
}

if($whatthreewords && !preg_match('/^[a-z]+\.[a-z]+\.[a-z]+$/i', $whatthreewords)) {
    respond(400, ['error' => 'Invalid whatthreewords format. Expected: word.word.word']);
}

if(($latitude !== null || $longitude !== null) && ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180)) {
    respond(400, ['error' => 'Invalid coordinates. Latitude must be between -90 and 90, longitude between -180 and 180']);
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
    $success = updateLocation($pdo, $locationId, $userId, $name, $whatthreewords, $latitude, $longitude);
    if($success) {
        respond(200, ['success' => true]);
    } else {
        respond(404, ['error' => 'Location not found or no changes made']);
    }
} catch(Throwable $e) {
    respond(500, ['error' => 'Could not update location']);
}
