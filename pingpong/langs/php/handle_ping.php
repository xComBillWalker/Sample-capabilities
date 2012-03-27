<?php
include_once 'avro.php';

//This code handles the ping message


// Get the posted message body
// NOTE: The message body is currently in Avro binary form
$post_data = file_get_contents("php://input");
$headers = getallheaders();

// Get the URI of the Avro schema on the OCL server
$schema_uri = $headers['X-XC-SCHEMA-URI'];

// Get the contents of the Avro schema identified by the URI retrieved above
$content = file_get_contents($schema_uri);

// Parse the Avro schema and place results in an AvroSchema object
$schema = AvroSchema::parse($content);

// Use Avro to decode and deserialize the binary-encoded message body.
// The result is the plain text version of the message body
// The message sender used Avro to binary-encode the text version of the message body before sending the message.

// Create an AvroIODatumReader object for the supplied AvroSchema.
// An AvroIODatumReader object handles schema-specific reading of data from the decoder and
// ensures that each datum read is consistent with the reader's schema.		
$datum_reader = new AvroIODatumReader($schema);

// Create an AvroStringIO object and assign it the encoded message body
$read_io = new AvroStringIO($post_data);

// Create an AvroIOBinaryDecoder object and assign it the $read_io object
$decoder = new AvroIOBinaryDecoder($read_io);

// Decode and deserialize the data using the schema and the supplied decoder
// The data is retrieved from the AvroStringIO object $read_io created above
// Upon return, $message contains the plain text version of the X.commerce message sent by the publisher
$message = $datum_reader->read($decoder);

$fp = fopen('test.log', 'at');

fwrite($fp,"Topic \n-----------\n");
fwrite($fp,"/message/ping");
fwrite($fp,"\nHeaders \n-----------\n");
fwrite($fp, print_r($headers,true));
fwrite($fp,"\n\nMessage \n-----------\n");
fwrite($fp, print_r($message,true));

$publisher_pseudonym = $headers['X-XC-PUBLISHER-PSEUDONYM'];

try {
	// Initialize a cURL session
	$ch = curl_init();
	
	// Set the cURL options for this session
	curl_setopt($ch, CURLOPT_URL, "https://localhost:8080/message/pong"); // URL of the target resource. This URL is the host:port of the Fabric, with the topic appended
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // stop cURL from verifying the peer's certificate when the https protocol is used
	curl_setopt($ch, CURLOPT_HEADER, true); // TRUE to include the header in the output
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // TRUE to return the transfer as a string of the return value of curl_exec() instead of outputting it out directly.
	curl_setopt($ch, CURLOPT_TIMEOUT, 10); // maximum number of seconds to allow cURL functions to execute
	curl_setopt($ch, CURLOPT_POST, true); // TRUE to do a regular HTTP POST. This POST is the normal application/x-www-form-urlencoded kind, most commonly used by HTML forms. 
	curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: avro/binary", "Authorization: Bearer ZN7UiioumrxSlbS1qdzbu0GH32mJIP/1vZWugLP/eOGonDYqcTz0/+1OyNVdviaC7rwkF9pP","X-XC-DESTINATION-ID: ".$publisher_pseudonym,"X-XC-SCHEMA-VERSION: 1.0.0", "X-XC-SCHEMA-URI: https://api.x.com/ocl/message/ping/1.0.0")); // An array of HTTP header fields to set, in the format array('Content-type: text/plain', 'Content-length: 100')
	
	// Add the binary-encoded, serialized message to the HTTP message as the message body
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data); // The full data to post in an HTTP "POST" operation.

	// POST the HTTP request to the Fabric and print the response returned by the Fabric
	$response = curl_exec($ch);
	fwrite($response);
	}
	catch (Exception $e) {
		echo "Error POSTing message to Fabric!";
		echo "Exception object:" . $e;
	} // end - try block
fclose($fp)

?>