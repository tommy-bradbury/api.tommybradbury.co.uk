<?php
$vendorDir = __DIR__;
$authBase =  __DIR__ . '/src/auth/';
$commonDir = '';
if(getenv('ENVIRONMENT') === 'development')
{
    $vendorDir .= '/../../..';
    $authBase = './';
    require_once __DIR__.'/functions.php';
    require_once __DIR__.'/enums.php';
}

define('VENDOR_DIR', $vendorDir . '/vendor');
define('AUTH_BASE',$authBase);
require_once VENDOR_DIR.'/autoload.php';
require_once __DIR__.'/functions.php';
require_once __DIR__.'/enums.php';

try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
    $dotenv->required(['DB_HOST', 'DB_USER', 'DB_PASS', 'NICKISGAY'])->notEmpty();
} catch(Dotenv\Exception\ValidationException $e) {
    http_response_code(503);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Service environment config is unavailable']);
    exit;
}
