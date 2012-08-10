<?php

//
// This module is part of the Pinger capability. The module: 
// -- handles the Pong message of the PingCapability transaction of the PingPong workflow
// -- sends the TransactionCompleted message in response, thereby ending the transaction
//

include_once 'avro.php';
include_once 'common.php';

// Get the message body out of the HTTP request message. The message body is currently in Avro binary form
$post_data = file_get_contents("php://input");

// Get the headers out of the HTTP request message
$headers = getallheaders();

$fp = fopen('ping_pong.log', 'at');

// Verify the message came from the Fabric
if ($headers['Authorization'] != FABRIC_CRED_PINGER) {
	fwrite($fp, "\n\nFATAL ERROR: Authorization header does not contain correct Fabric credentials.\n\n");
	fclose($fp);
	die(); // Terminate this script. In a more complete capability, return 403 to the Fabric
}

// Get the URI of the Avro schema from the OCL server. This URI is in the request header X-XC-SCHEMA-URI
$schema_uri = $headers['X-XC-SCHEMA-URI'];

// Deserialize the data in the Ping message's body
$content = file_get_contents($schema_uri);
$schema = AvroSchema::parse($content);
$datum_reader = new AvroIODatumReader($schema);
$read_io = new AvroStringIO($post_data);
$decoder = new AvroIOBinaryDecoder($read_io);
$message = $datum_reader->read($decoder);

// Get the full request URI
$request_uri = $_SERVER['REQUEST_URI'];

// Get the topic out of the request URI
if ( !($topic = strstr($request_uri, "/com.x.ecosystemmanagement.v1/PingPong/Pong"))) {
	fwrite($fp, "\n\nUnexpected topic: " . $request_uri . "\n\n");
	fclose($fp);
	die(); // Terminate the script. In a more complete capability, return 404 to the Fabric
}    

fwrite($fp, "\n\n(3) handle_pong.php:\n(a) Received a Pong message on topic " . $topic);

fwrite($fp,"\n\nPong Message Headers \n--------------------\n");
fwrite($fp, print_r($headers, true));

// Provide backward compatibility with earlier Fabric versions.
// Newer versions of the Fabric do not support the X-XC-PUBLISHER header
if (array_key_exists('X-XC-PUBLISHER', $headers)) {
	$publisher_pseudonym = $headers['X-XC-PUBLISHER'];
}
else {
	$publisher_pseudonym = $headers['X-XC-PUBLISHER-PSEUDONYM'];
}

// Send the TransactionCompleted message to complete the PingCapability transaction of the PingPong workflow
// NOTE: Because the TransactionCompleted message has no body, there is no need to use Avro to serialize anything.
try {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, FABRIC_URL . "/com.x.core.v1/TransactionCompleted");

	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // !!! Take this statement out before posting this code to the SampleCapability repo on github !!!

	curl_setopt($ch, CURLOPT_HEADER, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER,
			    array("Content-Type: avro/binary"
				     ,"Authorization: "       . TENANT_CRED_PINGER_TEST_TENANT
			         ,"X-XC-MESSAGE-GUID-CONTINUATION: " . $headers['X-XC-MESSAGE-GUID']
				     ,"X-XC-WORKFLOW-ID: "    . $headers['X-XC-WORKFLOW-ID']
				     ,"X-XC-TRANSACTION-ID: " . $headers['X-XC-TRANSACTION-ID']
				     ,"X-XC-DESTINATION-ID: " . $publisher_pseudonym
				     ,"X-XC-SCHEMA-VERSION: " . SCHEMA_VER_TRANS_COMPLETED));

	fwrite($fp, "\n\n(b) Sending a TransactionCompleted message on topic /com.x.core.v1/TransactionCompleted");
	
	// Send the HTTP request
	$response = curl_exec($ch);
	
	fwrite($fp, "\n\nHTTP response from the Fabric to the TransactionCompleted message:");
	fwrite($fp, "\n" . $response);
	fwrite($fp, LOG_SEPARATOR_STRING);
	fflush($fp);
}
catch (Exception $e) {
	echo "\n\nError sending HTTP POST request to the Fabric!";
	echo "\n\nException object:" . $e;
}

fclose($fp)

?>