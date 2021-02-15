<?php
// Global configs and Includes
include "global.php";

$mqAgent->clientStart();
$theQuestion = "Life, Love, the World and Everything ?";
echo "Sending Question, what is the answer to '".$theQuestion."' => ";

// Queue name is where the request is sent -> Set in global
$answer = $mqAgent->rpcClientRequest(['question' => $theQuestion], $queue_name);
echo print_r($answer['answer'], true);
