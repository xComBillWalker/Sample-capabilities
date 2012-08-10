<?php

//
// This module is part of the Pinger capability. The module: 
// -- handles the Pong message of the PingCapability transaction of the PingPong workflow
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

fwrite($fp, "\n\n(3) Pinger, handle_pong.php:\nReceived a message on topic " . $topic);

fwrite($fp,"\n\nPong Message Headers \n--------------------\n");
fwrite($fp, print_r($headers, true));
fwrite($fp, LOG_SEPARATOR_STRING);

fclose($fp)

?>