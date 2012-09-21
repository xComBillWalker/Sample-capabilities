<?php

//
// This module is part of the Pinger capability. It handles the Pong message sent by the Ponger capability
//

include_once 'avro.php';
include_once 'common.php';
include_once 'avro_encode_decode.php';

// Open the log file
$fp = fopen('ping_pong.log', 'at');

// Get the HTTP headers out of the request message just received
$headers_pong = getallheaders();

// Verify the message came from the Fabric
if ($headers_pong['Authorization'] != FABRIC_CRED_PINGER) {
	fwrite($fp, "\n\nFATAL ERROR: Authorization header does not contain correct Fabric credentials.\n\n");
	fclose($fp);
	die(); // Terminate this script. In a more complete capability, return 403 to the Fabric
}

// Get the full request URI
$request_uri = $_SERVER['REQUEST_URI'];

// Get the topic out of the request URI
if ( !($topic = strstr($request_uri, "/com.x.ecosystemmanagement.v2/PingPong/Pong"))) {
	fwrite($fp, "\n\nUnexpected topic: " . $request_uri . "\n\n");
	fclose($fp);
	die(); // Terminate the script. In a more complete capability, return 404 to the Fabric
}    

fwrite($fp, "\n\n(3) Pinger, handle_pong.php:\nReceived a message on topic " . $topic);

fwrite($fp,"\n\nPong Message Headers \n--------------------\n");
fwrite($fp, print_r($headers_pong, true));

// Get the message body out of the HTTP message and deserialize it.
$msg_body = file_get_contents("php://input");
$msg_body_unencoded = avro_decode($headers_pong['X-XC-SCHEMA-URI']   // URI of Pong message's schema on the OCL server
		                         ,$msg_body);

fwrite($fp, "\n\nUnencoded Pong Message: " . print_r($msg_body_unencoded, true) . "\n");

fwrite($fp, LOG_SEPARATOR_STRING);
fwrite($fp, "\n\nEnd PingCapability Transaction\nEnd PingPong Workflow");
fwrite($fp, LOG_SEPARATOR_STRING);

fclose($fp);
?>