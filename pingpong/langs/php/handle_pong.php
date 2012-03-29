<?php
include_once 'avro.php';

//This code handles the pong message


$post_data = file_get_contents("php://input");
$headers = getallheaders();
// Get the URI of the Avro schema on the OCL server
$schema_uri = $headers['X-XC-SCHEMA-URI'];

//deserialize the data
$content = file_get_contents($schema_uri);
$schema = AvroSchema::parse($content);
$datum_reader = new AvroIODatumReader($schema);
$read_io = new AvroStringIO($post_data);
$decoder = new AvroIOBinaryDecoder($read_io);
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