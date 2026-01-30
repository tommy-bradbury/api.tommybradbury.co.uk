<?php
error_reporting(E_ALL);
$vendorDir = __DIR__;
$locationsBase =  __DIR__.'/..';
$commonDir = $locationsBase.'/../common';
if(getenv('ENVIRONMENT') === 'development')
{
    $vendorDir .= '/../../..';
    $locationsBase = './';
    $commonDir = __DIR__.'/../../common';
}

define('VENDOR_DIR', $vendorDir.'/vendor');
define('COMMON_DIR',$commonDir);
define('LOCATIONS_BASE',$locationsBase);
require_once VENDOR_DIR.'/autoload.php';
require_once __DIR__.'/functions.php';
require_once __DIR__.'/enums.php';
require_once COMMON_DIR.'/commonFunctions.php';
require_once COMMON_DIR.'/commonEnums.php';
try {
    $dotenv = Dotenv\Dotenv::createImmutable('/var/task/src/locations/');
    $dotenv->load();
    $dotenv->required(['DB_HOST', 'DB_USER', 'DB_PASS'])->notEmpty();
} catch(Dotenv\Exception\ValidationException $e) {
    http_response_code(503);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Service environment config is unavailable']);
    exit;
}
