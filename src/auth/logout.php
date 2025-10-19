<?php
http_response_code(200);
header('Content-Type: application/json');
$data = ['response' => 'logout? Fuck off2!'];
echo json_encode($data);