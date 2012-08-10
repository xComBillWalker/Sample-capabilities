<?php

//
// This module is part of the Pinger capability.
// -- It handles the TransactionCompleted message. 
// -- This message ends the PingCapability transaction of the PingPong workflow.
//

include_once 'avro.php';
include_once 'common.php';

// Get the headers out of the request message. NOTE: This message has no message body
$headers = getallheaders();

$fp = fopen('ping_pong.log', 'at');

// Verify the message came from the Fabric
if ($headers['Authorization'] != FABRIC_CRED_PINGER) {
	fwrite($fp, "\n\nFATAL ERROR: Authorization header does not contain correct Fabric credentials.\n\n");
	fclose($fp);
	die(); // Terminate this script. In a more complete capability, return 403 to the Fabric
}

// Get the full request URI
$request_uri = $_SERVER['REQUEST_URI'];

// Get the topic out of the request URI
if ( !($topic = strstr($request_uri, "/com.x.core.v1/TransactionCompleted"))) {
	fwrite($fp, "Unexpected topic: " . $request_uri . "\n\n");
	fclose($fp);
	die(); // Terminate the script. In a more complete capability, return 404 to the Fabric
}    

fwrite($fp, "\n\n(5) Pinger, handle_transaction_completed.php:\nReceived a message on topic " . $topic);

fwrite($fp,"\n\nTransactionCompleted Message Headers\n------------------------------------\n");
fwrite($fp, print_r($headers, true));

fwrite($fp, LOG_SEPARATOR_STRING);
fwrite($fp, "\n\nEnd PingCapability Transaction\nEnd PingPong Workflow");
fwrite($fp, LOG_SEPARATOR_STRING);

fclose($fp)
?>