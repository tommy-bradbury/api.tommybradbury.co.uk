<?php
require __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/core/init.php';
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = strtok($uri, '?');
switch ($path) {
    case '/auth/signup':
        require AUTH_BASE . '/signup.php';
        break;
    case '/auth/login':
        require AUTH_BASE . '/login.php';
        break;
    case '/auth/logout':
        require AUTH_BASE . '/logout.php';
        break;
    case '/auth/status':
        setAccessControl();
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            header('Access-Control-Allow-Methods: POST, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type');
            http_response_code(204);
            exit;
        }
        header('Content-Type: application/json; charset=utf-8');
        validateJwtCookie($_ENV['APP_JWT_SECRET']);
        respond(200, []);
    default:
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Not Found lkjweyfgkuswaefygkwaseufygewafiu7yg', 'path' => $path]);
        break;
}

exit();
