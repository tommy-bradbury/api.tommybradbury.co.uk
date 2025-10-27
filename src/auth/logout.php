<?php

setAccessControl();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    http_response_code(204);
    exit;
}

header('Content-Type: application/json;');
header('charset=utf-8');
setcookie('session','', ['expires' => time() - 3600, 'path' => '/', 'domain' => '.tommybradbury.co.uk', 'secure' => true, 'httponly' => true, 'samesite' => 'Strict']);
respond(200, []);