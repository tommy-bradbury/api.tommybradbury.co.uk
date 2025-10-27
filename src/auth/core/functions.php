<?php

function setAccessControl(): void {
    header('Vary: Origin');
    header('Access-Control-Allow-Origin: https://tommybradbury.co.uk');
    header('Access-Control-Allow-Credentials: true');
}

function respond(int $httpResponseCode, array $response): never {
    http_response_code($httpResponseCode);
    echo json_encode($response);
    exit;
}