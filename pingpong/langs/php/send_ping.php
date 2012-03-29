<?php
include_once 'avro.php';

//Get the schema from the OCL cloud
//The schema url is normally of the form BASE_URL + topic + version

$schema_uri = "https://api.x.com/ocl/message/ping/1.0.0";
$content = file_get_contents($schema_uri);

//test message

$message = array(
	"payload" => "test"
);

// parse the schema, serialize into avro binary
$schema = AvroSchema::parse($content);
$datum_writer = new AvroIODatumWriter($schema);
$write_io = new AvroStringIO();
$encoder = new AvroIOBinaryEncoder($write_io);

try {	
	   $datum_writer->write($message, $encoder);
	}
	catch (Exception $e) {
		echo "Message does not adhere to schema!";
		echo "Exception object:" . $e;
	} 

	
try {
	
	$ch = curl_init();	
	curl_setopt($ch, CURLOPT_URL, "https://localhost:8080/message/ping"); 
	#make sure you are handling ssl certification if needed
	#that code is not shown here for brevity 
	
	curl_setopt($ch, CURLOPT_HEADER, true); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
	curl_setopt($ch, CURLOPT_TIMEOUT, 10); 
	curl_setopt($ch, CURLOPT_POST, true); 
	curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: avro/binary", "Authorization: Bearer ZN7UiioumrxSlbS1qdzbu0GH32mJIP/1vZWugLP/eOGonDYqcTz0/+1OyNVdviaC7rwkF9pP","X-XC-DESTINATION-ID: 24+4FfGxHYE+KsK6IVFMU0F7wLUYc+hy3mIGC92zF8eC7raTQ3pa3l6L3IE/PuaV92gz4eZc","X-XC-SCHEMA-VERSION: 1.0.0")); 
	curl_setopt($ch, CURLOPT_POSTFIELDS, $write_io->string()); 
	$response = curl_exec($ch);
	print $response;
	}
	catch (Exception $e) {
		echo "Error POSTing message to Fabric!";
		echo "Exception object:" . $e;
	} // end - try block
	


?>
