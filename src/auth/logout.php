<?php
setAccessControl();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    http_response_code(204);
    exit;
}

header('Content-Type: application/json;charset=utf-8');

$exp = time() - 3600;
if(getenv('ENVIRONMENT') === 'development') {
    setcookie(
        'session',
        '',
        ['expires' => $exp, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']
    );
} else {
    setcookie(
        'session',
        '',
        ['expires' => $exp, 'path' => '/', 'domain' => '.tommybradbury.co.uk', 'secure' => true, 'httponly' => true, 'samesite' => 'Strict']
    );

}
respond(200, []);