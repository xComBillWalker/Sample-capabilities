<?php

//
// About the PingPong Demo
// 
// The PingPong demo shows how to implement a pair of capabilities (Pinger and Ponger)
// that together implement the PingPong workflow of the Ecosystem Management XOCL package.
//
// The PingPong workflow consists of a single transaction named PingCapability. 
//
// This transaction is a response type transaction in which:
// -- 1. The Pinger capability sends a Ping message to a capability playing the Ponger role.
// -- 2. Upon receipt of the Ping message, the Ponger capability responds by sending a Pong message
//       back to the Pinger capability.
// -- 3. Upon receipt of the Pong message, the Pinger capability sends the Ponger capability
//       a TransactionCompleted message, thereby ending the transaction.
//
// NOTE: The PingPong demo does *not* show you everything required to implement a complete, 
//       choreographed capability. Instead, the demo covers the basics. To learn more, 
//       please read the documents on http://www.x.com.
// 

//
// This module is part of the Pinger capability. The module kicks off the PingPong workflow 
// by sending the Ping message of the PingCapability transaction of the PingPong workflow to the Ponger capability.
//

include_once 'avro.php';
include_once 'guid_gen.php';
include_once 'common.php';

// Get the Avro message schema from the XOCL server for the message associated with the Ping topic 
$json_schema = file_get_contents("https://api.x.com/ocl/com.x.ecosystemmanagement.v1/PingPong/Ping/" . SCHEMA_VER_PING);

// Initialize the Ping message
$message = array(
	"payload" => "test"
);

// Serialize the Ping message into Avro binary format
$avro_schema  = AvroSchema::parse($json_schema);
$datum_writer = new AvroIODatumWriter($avro_schema);
$write_io     = new AvroStringIO();
$encoder      = new AvroIOBinaryEncoder($write_io);

try {	
	$datum_writer->write($message, $encoder);
}
catch (Exception $e) {
	echo "The Ping message does not adhere to the schema!";
	echo "Exception object:" . $e;
} 

// Open the log file
$fp = fopen('ping_pong.log', 'at');
fwrite($fp, "\n\nXOCL Workflow:    com.x.ecosystemmanagement.v1.PingPong");
fwrite($fp, "\nXOCL Transaction: com.x.ecosystemmanagement.v1.PingCapability");
fwrite($fp, "\n\n(1) send_ping.php:\nSending a Ping message on topic /com.x.ecosystemmanagement.v1/PingPong/Ping");

// Send the Ping message of PingCapability transaction of the PingPong workflow
try {
	$ch = curl_init();	
	curl_setopt($ch, CURLOPT_URL, FABRIC_URL . "/com.x.ecosystemmanagement.v1/PingPong/Ping"); 
	
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // !!! Take this statement out before posting this code to the SampleCapability repo on github !!!
		
	curl_setopt($ch, CURLOPT_HEADER, true); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
	curl_setopt($ch, CURLOPT_TIMEOUT, 10); 
	curl_setopt($ch, CURLOPT_POST, true);

	$headers = array("Content-Type: avro/binary"
			         ,"Authorization: "       . TENANT_CRED_PINGER_TEST_TENANT
	    	   		 ,"X-XC-MESSAGE-GUID-CONTINUATION: "
	    	   		 ,"X-XC-WORKFLOW-ID: "    . guid()
	    	   		 ,"X-XC-TRANSACTION-ID: " . guid()
			   		 ,"X-XC-DESTINATION-ID: " . CAPABILITY_ID_PONGER
			   		 ,"X-XC-SCHEMA-VERSION: " . SCHEMA_VER_PING);
	
	fwrite($fp,"\n\nPing Message Headers \n--------------------\n");
	fwrite($fp, print_r($headers, true));
		
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	
	// Add the serialized data to the HTTP request as its message body
	curl_setopt($ch, CURLOPT_POSTFIELDS, $write_io->string()); 
	
	// Send the HTTP request
	$response = curl_exec($ch);
	
	fwrite($fp, "\n\nHTTP response from the Fabric to the Ping message:");
	fwrite($fp, "\n" . $response);
	fwrite($fp, LOG_SEPARATOR_STRING);
    fflush($fp);
	
	print "HTTP response from the Fabric to the Ping message:\n";
	print $response;	
}
catch (Exception $e) {
	print "\n\nError sending HTTP POST request to the Fabric!";
	print "\n\nException object:" . $e;
} // end - try block

fclose($fp);
?>
