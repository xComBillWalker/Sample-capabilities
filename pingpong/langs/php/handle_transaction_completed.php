<?php

//
// This module is part of the Ponger capability.
// -- It handles the TransactionCompleted message. 
// -- This message ends the PingCapability transaction of the PingPong workflow.
//

include_once 'avro.php';
include_once 'common.php';

// Get the headers out of the request message. NOTE: This message has no message body
$headers = getallheaders();

$fp = fopen('ping_pong.log', 'at');

// Verify the message came from the Fabric
if ($headers['Authorization'] != FABRIC_CRED_PONGER) {
	fwrite($fp, "\n\nFATAL ERROR: Authorization header does not contain correct Fabric credentials.\n\n");
	fclose($fp);
	die(); // terminate the script
}

// Get the full request URI
$request_uri = $_SERVER['REQUEST_URI'];

// Get the topic out of the request URI
if ( !($topic = strstr($request_uri, "/com.x.core.v1/TransactionCompleted"))) {
	fwrite($fp, "Unexpected topic: " . $request_uri . "\n\n");
	fclose($fp);
	die();
}    

fwrite($fp, "\n\n(4) handle_transaction_completed.php:\nReceived a TransactionCompleted message on topic " . $topic);

fwrite($fp,"\n\nTransactionCompleted Message Headers\n------------------------------------\n");
fwrite($fp, print_r($headers, true));

fwrite($fp, LOG_SEPARATOR_STRING);
fwrite($fp, LOG_SEPARATOR_STRING);

fclose($fp)
?>