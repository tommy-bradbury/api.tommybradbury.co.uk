<?php
require_once __DIR__ . '/core/init.php';
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = strtok($uri, '?');

switch ($path) {
    case '/auth/login':
        require AUTH_BASE . 'login.php';
        break;
    case '/auth/logout':
        require AUTH_BASE . 'logout.php';
        break;
    default:
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Not Found lkjweyfgkuswaefygkwaseufygewafiu7yg', 'path' => $path]);
        break;
}

exit();
