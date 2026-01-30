<?php
require __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/core/init.php';
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = strtok($uri, '?');
switch ($path) {
    case '/locations/create':
        require LOCATIONS_BASE . '/create.php';
        break;
    case '/locations/update':
        require LOCATIONS_BASE . '/update.php';
        break;
    case '/locations/delete':
        require LOCATIONS_BASE . '/delete.php';
        break;
    case '/locations':
    case '/locations/':
        require LOCATIONS_BASE . '/list.php';
        break;
    default:
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Not Found', 'path' => $path]);
        break;
}

exit();
