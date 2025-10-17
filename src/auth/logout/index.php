<?php

$response = [
    'statusCode' => 200,
    'headers' => [
        'Content-Type' => 'application/json',
    ],
    // The required body content, as requested
    'body' => json_encode(['response' => 'logout? Fuck off2!']),
];

return $response;