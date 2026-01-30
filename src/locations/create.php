<?php

setAccessControl();
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Methods: POST, OPTIONS');
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
$name = isset($req['name']) ? (string)$req['name'] : null;
$whatthreewords = isset($req['whatthreewords']) ? (string)$req['whatthreewords'] : null;
$latitude = isset($req['latitude']) ? (float)$req['latitude'] : null;
$longitude = isset($req['longitude']) ? (float)$req['longitude'] : null;

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

try {
    $locationId = createLocation($pdo, $userId, $name, $whatthreewords, $latitude, $longitude);
    respond(201, ['id' => $locationId]);
} catch(Throwable $e) {
    respond(500, ['error' => 'Could not create location']);
}
