<?php
include_once 'avro.php';

//This example demonstrate how to publish a message on an
//approved topic supported by OCL schema.
//https://api.x.com/ocl/message/ping/1.0.0


//Get the schema from the OCL cloud
//The schema url is normally of the form BASE_URL + topic + version
//The base url in this case is https://api.x.com and topic is /marketplace/profile/get 
//and version is 1.0
$schema_uri = "https://api.x.com/ocl/message/ping/1.0.0";
$content = file_get_contents($schema_uri);
if ($content !== false) {
	echo "Successfully read the schema!\n";	
}

//construct the JSON message which we will encode to avro binary
//remember that this message should adhere to the schema

$message = array(
	"payload" => "test"
);

// parse the schema
$schema = AvroSchema::parse($content);

// Create an AvroIODataWriter object for the supplied AvroSchema. 
// An AvroIODataWriter object handles schema-specific writing of data to the encoder and
// ensures that each datum written is consistent with the writer's schema.
$datum_writer = new AvroIODatumWriter($schema);

// Create an AvroStringIO object - this is an AvroIO wrapper for string I/O
$write_io = new AvroStringIO();

// Create an AvroIOBinaryEncoder object.
// This object encodes and writes Avro data to the supplied AvroIO object using Avro binary encoding.
$encoder = new AvroIOBinaryEncoder($write_io);

try {
		// Binary-encode and serialize the supplied  message using the schema and the supplied encoder
		// The result is stored in the AvroStringIO object $write_io created above
		$datum_writer->write($message, $encoder);
	}
	catch (Exception $e) {
		echo "Message does not adhere to schema!";
		echo "Exception object:" . $e;
	} // end - try block
	
	//

	
try {
	// Initialize a cURL session
	$ch = curl_init();
	
	// Set the cURL options for this session
	curl_setopt($ch, CURLOPT_URL, "https://localhost:8080/message/ping"); // URL of the target resource. This URL is the host:port of the Fabric, with the topic appended
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // stop cURL from verifying the peer's certificate when the https protocol is used
	curl_setopt($ch, CURLOPT_HEADER, true); // TRUE to include the header in the output
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // TRUE to return the transfer as a string of the return value of curl_exec() instead of outputting it out directly.
	curl_setopt($ch, CURLOPT_TIMEOUT, 10); // maximum number of seconds to allow cURL functions to execute
	curl_setopt($ch, CURLOPT_POST, true); // TRUE to do a regular HTTP POST. This POST is the normal application/x-www-form-urlencoded kind, most commonly used by HTML forms. 
	curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: avro/binary", "Authorization: Bearer ZN7UiioumrxSlbS1qdzbu0GH32mJIP/1vZWugLP/eOGonDYqcTz0/+1OyNVdviaC7rwkF9pP","X-XC-DESTINATION-ID: 24+4FfGxHYE+KsK6IVFMU0F7wLUYc+hy3mIGC92zF8eC7raTQ3pa3l6L3IE/PuaV92gz4eZc","X-XC-SCHEMA-VERSION: 1.0.0", "X-XC-SCHEMA-URI: https://api.x.com/ocl/message/ping/1.0.0")); // An array of HTTP header fields to set, in the format array('Content-type: text/plain', 'Content-length: 100')
	
	// Add the binary-encoded, serialized  message to the HTTP message as the message body
	curl_setopt($ch, CURLOPT_POSTFIELDS, $write_io->string()); // The full data to post in an HTTP "POST" operation.

	// POST the HTTP request to the Fabric and print the response returned by the Fabric
	$response = curl_exec($ch);
	print $response;
	}
	catch (Exception $e) {
		echo "Error POSTing message to Fabric!";
		echo "Exception object:" . $e;
	} // end - try block
	


?>
