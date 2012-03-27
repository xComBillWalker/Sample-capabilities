<?php
include_once 'avro.php';

//This code handles the pong message

// Get the posted message body
// NOTE: The message body is currently in Avro binary form
$post_data = file_get_contents("php://input");
$headers = getallheaders();

// Get the URI of the Avro schema on the OCL server 
$schema_uri = $headers['X-XC-SCHEMA-URI'];

// Get the contents of the Avro schema identified by the URI retrieved above
$content = file_get_contents($schema_uri);

// Parse the  Avro schema and place results in an AvroSchema object
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

// Decode and deserialize the data using the  schema and the supplied decoder
// The data is retrieved from the AvroStringIO object $read_io created above
// Upon return, $message contains the plain text version of the X.commerce message sent by the publisher
$message = $datum_reader->read($decoder);

$fp = fopen('test.log', 'at');

fwrite($fp,"Topic \n-----------\n");
fwrite($fp,"/message/pong");
fwrite($fp,"\nHeaders \n-----------\n");
fwrite($fp, print_r($headers,true));
fwrite($fp,"\n\nMessage \n-----------\n");
fwrite($fp, print_r($message,true));
fclose($fp)


?>