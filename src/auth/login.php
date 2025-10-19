<?php
http_response_code(200);
header('Content-Type: application/json');
$data = ['response' => 'login? Fuck off2!'];
echo json_encode($data);