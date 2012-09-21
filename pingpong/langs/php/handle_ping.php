<?php

//
// This module is part of the Ponger capability. The module:
// -- receives the Ping message sent by the Pinger capability
// -- responds by sending a Pong message to the Pinger capability 
//

include_once 'avro.php';
include_once 'common.php';
include_once 'avro_encode_decode.php';

// Give send_ping a chance to finish writing to the common log file - ping_pong.log 
usleep(500000); // 1/2 second

// Open the log file
$fp = fopen('ping_pong.log', 'at');

// Get the HTTP headers out of the request message just received
$headers_ping = getallheaders();

// Verify that the message came from the Fabric
if ($headers_ping['Authorization'] != FABRIC_CRED_PONGER) {
	fwrite($fp, "\n\nPonger, handle_ping.php:\nFATAL ERROR: Authorization header does not contain correct Fabric credentials.\n\n");
	fclose($fp);
	die(); // Terminate this script. In a more complete capability, return 403 to the Fabric
}

// Get the full request URI
$request_uri = $_SERVER['REQUEST_URI'];

// Get the topic out of the request URI
if ( !($topic = strstr($request_uri, "/com.x.ecosystemmanagement.v2/PingPong/Ping"))) {
	fwrite($fp, "\n\nPonger, handle_ping.php:\nUnexpected topic: " . $request_uri . "\n\n");
	fclose($fp);
	die();
}    

fwrite($fp, "\n\n(2) Ponger, handle_ping.php:\n(a) Received a message on topic " . $topic);

fwrite($fp,"\n\nPing Message Headers \n--------------------\n");
fwrite($fp, print_r($headers_ping, true));

// Get the message body out of the HTTP request and deserialize it. 
$msg_body = file_get_contents("php://input");
$msg_body_unencoded = avro_decode($headers_ping['X-XC-SCHEMA-URI']   // URI of Ping message's schema on the OCL server
	                             ,$msg_body);

fwrite($fp, "\n\nUnencoded Ping Message: " . print_r($msg_body_unencoded, true) . "\n");

// Ensure backward compatibility with earlier Fabric versions. 
// Newer versions of the Fabric do not support the X-XC-PUBLISHER header
if (array_key_exists('X-XC-PUBLISHER', $headers_ping)) {
	$publisher_pseudonym = $headers_ping['X-XC-PUBLISHER'];	
}
else {
	$publisher_pseudonym = $headers_ping['X-XC-PUBLISHER-PSEUDONYM'];
}

// Send the Pong message of the PingCapability transaction to the Pinger capability (via the Fabric)
// NOTE: Since the Pong message's body must be a copy of the Ping message body, there is no need to use Avro to serialize anything.
try {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, FABRIC_URL . "/com.x.ecosystemmanagement.v2/PingPong/Pong");
	
//	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

	curl_setopt($ch, CURLOPT_HEADER, true); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
	curl_setopt($ch, CURLOPT_TIMEOUT, 10); 
	curl_setopt($ch, CURLOPT_POST, true);

	// Define the headers for the HTTP request that will carry the Pong message as its payload
	$headers_pong = array("Content-Type: avro/binary"
			             ,"Authorization: "       . TENANT_CRED_PONGER_TEST_TENANT
			             ,"X-XC-MESSAGE-GUID-CONTINUATION: " . $headers_ping['X-XC-MESSAGE-GUID']
			             ,"X-XC-WORKFLOW-ID: "    . $headers_ping['X-XC-WORKFLOW-ID']
			             ,"X-XC-TRANSACTION-ID: " . $headers_ping['X-XC-TRANSACTION-ID']
			             ,"X-XC-DESTINATION-ID: " . $publisher_pseudonym
			             ,"X-XC-SCHEMA-VERSION: " . SCHEMA_VER_PONG);
	
	// Add the HTTP headers to the request message.
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers_pong);

	// Use the original message body of Ping message as the Pong messsage's body
	// The PingPong workflow requires that the Pong message have the same payload as the Ping message
	curl_setopt($ch, CURLOPT_POSTFIELDS, $msg_body);
	
	fwrite($fp, "\n\n(2)(b) Sending a Pong message on topic /com.x.ecosystemmanagement.v2/PingPong/Pong");
	
	// Send the HTTP POST request to the Fabric
	$response = curl_exec($ch);
	
	fwrite($fp, "\n\nHTTP response from the Fabric to the Pong message:");
	fwrite($fp, "\n" . $response);
	fwrite($fp, LOG_SEPARATOR_STRING);
}
catch (Exception $e) {
	print "\n\nPonger, handle_ping.php:\nError sending HTTP POST request to the Fabric!";
	print "\n\nException object:" . print_r($e, true);
}

fclose($fp);
?>