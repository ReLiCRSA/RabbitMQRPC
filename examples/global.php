<?php

use ReLiCRSA\RabbitRPC;

// Composer Autoload
include "../vendor/autoload.php";

// These assume the defaults as in the docker
$userName = 'guest';
$password = 'guest';
$rabbitHost = 'localhost';
$rabbitVhost = "/";
$rabbitPort = 5672;
$queue_name = "rabbit_queue_name";

// Start the agent
$mqAgent = new RabbitRPC($rabbitHost, $userName, $password, $rabbitVhost, $rabbitPort);
