<?php

$vendorDir = __DIR__;
$authBase =  __DIR__ . '/src/auth/';
if(getenv('ENVIRONMENT') === 'development')
{
    $vendorDir .= '/../..';
    $authBase = './';
}
define('VENDOR_DIR', $vendorDir . '/vendor');
define('AUTH_BASE',$authBase);

require VENDOR_DIR.'/autoload.php';
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = strtok($uri, '?');

switch ($path) {
    case '/auth/login':
        require AUTH_BASE . 'login.php';
        break;
    case '/auth/logout':
        require AUTH_BASE . 'logout.php';
        break;
    case '/auth/rohan':
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Fuck off Rohan', 'path' => $path]);
        break;
    case '/auth/jason':
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'it works u cunt', 'path' => $path]);
        break;
    default:
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Not Found lkjweyfgkuswaefygkwaseufygewafiu7yg', 'path' => $path]);
        break;
}

exit();
