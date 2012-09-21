<?php

//
// About the PingPong Demo
// 
// The PingPong demo shows you how to implement a pair of capabilities (Pinger and Ponger)
// that together implement the PingPong workflow of the com.x.ecosystemmanagement.v2 XOCL package.
//
// The PingPong workflow consists of a single transaction named PingCapability. 
//
// This transaction is a response type transaction in which:
// -- 1. The Pinger capability sends a Ping message to a capability playing the Ponger role.
// -- 2. Upon receipt of the Ping message, the Ponger capability:
//       -- writes the message headers and decoded message body to the log file. 
//       -- sends a Pong message to the Pinger capability
// -- 3. Upon receipt of the Pong message, the Pinger capability writes the message headers and  
//       decoded message body to the log file
//
// NOTE: The PingPong demo does *not* show you everything required to implement a complete, 
//       choreographed capability. Instead, the demo covers the basics. 
//       To learn more, please read the documentation on http://www.x.com.
// 

//
// This module is part of the Pinger capability. The module kicks off the PingPong workflow 
// by sending the Ping message of the PingCapability transaction to the Ponger capability.
//

include_once 'avro.php';
include_once 'guid_gen.php';
include_once 'common.php';
include_once 'avro_encode_decode.php';

// Open the log file
$fp = fopen('ping_pong.log', 'at');

// In the log, identify the XOCL workflow and transaction being executed
fwrite($fp, "\n\nXOCL Workflow:    com.x.ecosystemmanagement.v2.PingPong");
fwrite($fp, "\nXOCL Transaction: com.x.ecosystemmanagement.v2.PingPong.PingCapability");

// Build the URI of the Avro schema on the OCL server of the Ping message
$schema_uri = "https://api.x.com/ocl/com.x.ecosystemmanagement.v2/PingPong/Ping/" . SCHEMA_VER_PING;

// Initialize the Ping message
$ping_msg = array(
	"payload" => "test"
);

// Avro-encode the Ping message
$ping_msg_encoded = avro_encode($schema_uri, $ping_msg);

// Use cURL to send the Ping message of the PingCapability transaction to the Fabric
try {
	$ch = curl_init();	
	curl_setopt($ch, CURLOPT_URL, FABRIC_URL . "/com.x.ecosystemmanagement.v2/PingPong/Ping"); 
	
//	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
		
	curl_setopt($ch, CURLOPT_HEADER, true); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
	curl_setopt($ch, CURLOPT_TIMEOUT, 10); 
	curl_setopt($ch, CURLOPT_POST, true);

	// Define the headers for the HTTP request that will carry the Ping message as its payload
	$headers_ping = array("Content-Type: avro/binary"
			             ,"Authorization: "       . TENANT_CRED_PINGER_TEST_TENANT
			             ,"X-XC-MESSAGE-GUID-CONTINUATION: "
			             ,"X-XC-WORKFLOW-ID: "    . guid()
			             ,"X-XC-TRANSACTION-ID: " . guid()
			             ,"X-XC-DESTINATION-ID: " . CAPABILITY_ID_PONGER
			             ,"X-XC-SCHEMA-VERSION: " . SCHEMA_VER_PING);
	
 	fwrite($fp, "\n\n(1) Pinger, send_ping.php:\nSending a Ping message on topic /com.x.ecosystemmanagement.v2/PingPong/Ping");
	fwrite($fp,"\n\nPing Message Headers \n--------------------\n");
	fwrite($fp, print_r($headers_ping, true));

	fwrite($fp, "\n\nUnencoded Ping Message: " . print_r($ping_msg, true) . "\n");
	
    // Add the HTTP headers to the request message.
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers_ping);
	
	// Add the Avro-encoded Ping message to the HTTP request as its payload
	curl_setopt($ch, CURLOPT_POSTFIELDS, $ping_msg_encoded);
	
	// Send the HTTP POST request to the Fabric
	$response = curl_exec($ch);
	
	fwrite($fp, "\n\nHTTP response from the Fabric to the Ping message:");
	fwrite($fp, "\n" . $response);
	fwrite($fp, LOG_SEPARATOR_STRING);
	
	print "HTTP response from the Fabric to the Ping message:\n";
	print $response;	
}
catch (Exception $e) {
	print "\n\nPinger, send_ping.php:\nError sending HTTP POST request to the Fabric!";
	print "\n\nException object:" . print_r($e, true);
} // end - try block

fclose($fp);
?>
