<?php

//
// avro_encode: Gets the Avro message schema identified by the URI passed in 
//              and uses it to serialize the supplied data and encode it into avro/binary format.
//
// Parameters:
// -- $schema_uri - the URI of the Avro schema on the OCL server to retrieve
// -- $data_unencoded - the data to serialize into avro/binary format
//
// Returns - a serialized, avro/binary encoded form of the supplied data
//  
function avro_encode($schema_uri, $data_unencoded) {

	// Get the Avro schema (in JSON format) identified by the URI passed in from the OCL server
	$json_schema = file_get_contents($schema_uri);

	// Use the JSON formatted Avro schema to create an AvroSchema object
	$avro_schema  = AvroSchema::parse($json_schema);
	
	$datum_writer = new AvroIODatumWriter($avro_schema);
	$write_io     = new AvroStringIO();
	$encoder      = new AvroIOBinaryEncoder($write_io);

	// Serialize the unencoded data passed in and encode it into avro/binary format
	try {
		$datum_writer->write($data_unencoded, $encoder);
	}
	catch (Exception $e) {
		echo "\n\nPinger, send_ping.php:\nThe supplied message does not adhere to the Avro schema!";
		echo "Exception object:" . $e;
	}
	
    // Return the Avro-encoded message as a string
	return $write_io->string();
}

//
// avro_decode: Gets the Avro message schema identified by the URI passed in
//              and uses it to deserialize the supplied data and decode it from avro/binary format.
//
// Parameters:
// -- $schema_uri   - the URI of the Avro schema on the OCL server to retrieve
// -- $data_encoded - the data to deserialize and decode
//
// Returns - a deserialized, unencoded form of the supplied data
//
function avro_decode($schema_uri, $data_encoded) {
	
	// Get the Avro schema (in JSON format) identified by the URI passed in from the OCL server
	$json_schema  = file_get_contents($schema_uri);

	// Use the JSON formatted Avro schema to create an AvroSchema object
	$avro_schema  = AvroSchema::parse($json_schema);

	// Create an AvroIODatumReader object for the supplied Avro schema object.
	$datum_reader = new AvroIODatumReader($avro_schema);
	
	// Create an AvroStringIO object and assign it the Avro-encoded message body passed in
	$read_io      = new AvroStringIO($data_encoded);
	
	// Create an AvroIOBinaryDecoder object.
	$decoder      = new AvroIOBinaryDecoder($read_io);
	
	// Decode and deserialize the message body passed in and return it to the caller
	return $datum_reader->read($decoder);
}
?>