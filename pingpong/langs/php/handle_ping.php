<?php
include_once 'avro.php';

//This code handles the ping message


// Get the posted message body
// NOTE: The message body is currently in Avro binary form
$post_data = file_get_contents("php://input");
$headers = getallheaders();

$fp = fopen('test.log', 'at');

fwrite($fp,"Topic \n-----------\n");
fwrite($fp,"/message/ping");
fwrite($fp,"\nHeaders \n-----------\n");
fwrite($fp, print_r($headers,true));

//provide backward compatibility. new version of the fabric
//do not support X-XC-PUBLISHER
if (array_key_exists('X-XC-PUBLISHER',$headers)) {
	$publisher_pseudonym = $headers['X-XC-PUBLISHER'];	
}
else {
	$publisher_pseudonym = $headers['X-XC-PUBLISHER-PSEUDONYM'];
}

try {
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://localhost:8080/message/pong"); 
	curl_setopt($ch, CURLOPT_HEADER, true); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
	curl_setopt($ch, CURLOPT_TIMEOUT, 10); 
	curl_setopt($ch, CURLOPT_POST, true); 
	curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: avro/binary", "Authorization: Bearer ZN7UiioumrxSlbS1qdzbu0GH32mJIP/1vZWugLP/eOGonDYqcTz0/+1OyNVdviaC7rwkF9pP","X-XC-DESTINATION-ID: ".$publisher_pseudonym,"X-XC-SCHEMA-VERSION: 1.0.0", "X-XC-SCHEMA-URI: https://api.x.com/ocl/message/ping/1.0.0")); 
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	$response = curl_exec($ch);
	fwrite($fp, $response);
	}
	catch (Exception $e) {
		echo "Error POSTing message to Fabric!";
		echo "Exception object:" . $e;
	} 
fclose($fp)

?>