<?php

//
// This module handles a /message/failed message for either Pinger or Ponger.
//

include_once 'avro.php';
include_once 'common.php';
include_once 'avro_encode_decode.php';

// Give the calling script a chance to finish writing to the common log file - ping_pong.log
usleep(500000); // 1/2 second

// Open the log file
$fp = fopen('ping_pong.log', 'at');

// Get the HTTP headers out of the /message/failed request message
$headers_msg_failed = getallheaders();

// Verify the message came from the Fabric
if (($headers_msg_failed['Authorization'] != FABRIC_CRED_PINGER) && ($headers_msg_failed['Authorization'] != FABRIC_CRED_PONGER)) {
	fwrite($fp, "\n\nhandle_message_failed.php: FATAL ERROR: Authorization header does not contain correct Fabric credentials.\n\n");
	fclose($fp);
	die(); // Terminate this script. In a more complete capability, return 403 to the Fabric
}

// Get the full request URI
$request_uri = $_SERVER['REQUEST_URI'];

// Get the topic out of the request URI
if ( !($topic = strstr($request_uri, "/message/failed"))) {
	fwrite($fp, "\n\nhandle_message_failed.php: Unexpected topic: " . $request_uri . "\n\n");
	fclose($fp);
	die(); // Terminate the script. In a more complete capability, return 403 to the Fabric
}

fwrite($fp, "\n\nhandle_message_failed.php:\nReceived a message on topic " . $topic);

fwrite($fp,"\n\n/message/failed Headers\n------------------------------------\n");
fwrite($fp, print_r($headers_msg_failed, true));
    
// Get the message body from the HTTP message and deserialize it.
$msg_body  = file_get_contents("php://input");
$error_msg = avro_decode($headers_msg_failed['X-XC-SCHEMA-URI']   // URI of MessageFailed message's schema on the OCL server
		                ,$msg_body);

fwrite($fp,"\n\nUnencoded /message/failed message: " . print_r($error_msg, true) . "\n");
fwrite($fp, LOG_SEPARATOR_STRING);

fclose($fp)
?>