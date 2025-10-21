<?php

require __DIR__ . '/vendor/autoload.php';
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = strtok($uri, '?');

switch ($path) {
    case '/auth/login':
        require __DIR__ . 'login.php';
        break;
    case '/auth/logout':
        require __DIR__ . 'logout.php';
        break;
    case '\/auth\/rohan':
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Fuck off Rohan', 'path' => $path]);
        break;
    default:
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Not Found lkjweyfgkuswaefygkwaseufygewafiu7yg', 'path' => $path]);
        break;
}

exit();