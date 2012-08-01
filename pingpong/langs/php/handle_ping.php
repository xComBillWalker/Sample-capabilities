<?php

//
// This module is part of the Ponger capability. The module: 
// -- handles the Ping message of the PingCapability transaction of the PingPong workflow
// -- sends the Pong message of the PingCapability transaction of the PingPong workflow in response
//

include_once 'avro.php';
include 'common.php';

// Get the message body out of the HTTP request message. The message body is currently in Avro binary form
$post_data = file_get_contents("php://input");

// Get the headers out of the HTTP request message
$headers = getallheaders();

// Open the log file
$fp = fopen('ping_pong.log', 'at');

// Verify the message came from the Fabric
if ($headers['Authorization'] != FABRIC_CRED_PONGER) {
	fwrite($fp, "\n\nFATAL ERROR: Authorization header does not contain correct Fabric credentials.\n\n");
	fclose($fp);
	die(); // terminate this script
}

// Get the full request URI
$request_uri = $_SERVER['REQUEST_URI'];

// Get the topic out of the request URI
if ( !($topic = strstr($request_uri, "/com.x.ecosystemmanagement.v1/PingPong/Ping"))) {
	fwrite($fp, "\n\nUnexpected topic: " . $request_uri . "\n\n");
	fclose($fp);
	die();
}    

fwrite($fp, "\n\n(2) handle_ping.php:\n(a) Received a Ping message on topic " . $topic);

fwrite($fp,"\n\nPing Message Headers \n--------------------\n");
fwrite($fp, print_r($headers, true));

// Ensure backward compatibility with earlier Fabric versions. 
// Newer versions of the Fabric do not support the X-XC-PUBLISHER header
if (array_key_exists('X-XC-PUBLISHER', $headers)) {
	$publisher_pseudonym = $headers['X-XC-PUBLISHER'];	
}
else {
	$publisher_pseudonym = $headers['X-XC-PUBLISHER-PSEUDONYM'];
}

// Send the Pong message of PingCapability transaction of the PingPong workflow
// NOTE: Since the Pong's message body is a copy of the Ping message body, there is no need to use Avro to serialize anything.
try {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, FABRIC_URL . "/com.x.ecosystemmanagement.v1/PingPong/Pong");
	
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // !!! Take this statement out before posting this code to the SampleCapability repo on github !!!
	
	curl_setopt($ch, CURLOPT_HEADER, true); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
	curl_setopt($ch, CURLOPT_TIMEOUT, 10); 
	curl_setopt($ch, CURLOPT_POST, true); 
	curl_setopt($ch, CURLOPT_HTTPHEADER, 
			    array("Content-Type: avro/binary"
			         ,"Authorization: "       . TENANT_CRED_PONGER_TEST_TENANT
			    	 ,"X-XC-MESSAGE-GUID-CONTINUATION: " . $headers['X-XC-MESSAGE-GUID']
			    	 ,"X-XC-WORKFLOW-ID: "    . $headers['X-XC-WORKFLOW-ID']
			    	 ,"X-XC-TRANSACTION-ID: " . $headers['X-XC-TRANSACTION-ID']
			         ,"X-XC-DESTINATION-ID: " . $publisher_pseudonym
			         ,"X-XC-SCHEMA-VERSION: " . SCHEMA_VER_PONG));

	// Use the message body of Ping message as the Pong messsage's body
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	
	fwrite($fp, "\n\n(b) Sending a Pong message on topic /com.x.ecosystemmanagement.v1/PingPong/Pong");
	
	// Send the HTTP request
	$response = curl_exec($ch);
	
	fwrite($fp, "\n\nHTTP response from the Fabric to the Pong message:");
	fwrite($fp, "\n" . $response);
	fwrite($fp, LOG_SEPARATOR_STRING);
	fflush($fp);
}
catch (Exception $e) {
	echo "\n\nError sending HTTP POST request to the Fabric!";
	echo "\n\nException object:" . $e;
}
 
fclose($fp);
?>