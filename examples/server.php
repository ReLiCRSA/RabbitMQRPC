<?php
// Global configs and Includes
include "global.php";

// Queue name is where the request is received -> Set in global
$mqAgent->serverQueue('serverProcess', $queue_name);
echo "Starting Server:\r\n";
$mqAgent->serverStart();

function serverProcess($request)
{
    $requestBody = json_encode($request->body);
    echo "Request -> ".print_r($requestBody, true)."\r\n";
    return ['answer' => '42'];
}