<?php
// Sample php file to send a ping message to the XFabric


function sendPing($message)
{
    // Get variables
    include('variables.php');

    // Get Avro and encode the message
    include_once('avro.php');
    $content = file_get_contents($schema_uri);
    $schema = AvroSchema::parse($content);
    $datum_writer = new AvroIODatumWriter($schema);
    $write_io = new AvroStringIO();
    $encoder = new AvroIOBinaryEncoder($write_io);

    $current = "<h2>Sending Ping</h2>".date('Y-m-d h:i:s')."\n";

    try
    {
        $datum_writer->write($message, $encoder);
        $message = $write_io->string();

        try
        {
            // Set up cURL
            $curl = curl_init();  // Initiate it

            // Send a header
            curl_setopt($curl, CURLOPT_HEADER, TRUE);

            // Send as POST
            curl_setopt($curl, CURLOPT_POST, TRUE);

            // Don't worry about ssl stuff for now
            // (You'll want to when you've got a full capability up and running)
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);

            // Stop cURL if it takes longer than 5 seconds
            curl_setopt($curl, CURLOPT_TIMEOUT, 5);

            // Give returned information back to cURL
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);


            // Craft cURL data

            // Address XFabric and topic
            curl_setopt($curl, CURLOPT_URL, $fabricURL."/message/ping");

            // Set message as POST
            curl_setopt($curl, CURLOPT_POSTFIELDS, $message);

            // Set the headers
            curl_setopt($curl, CURLOPT_HTTPHEADER,
                array(
                    // SELF Auth data.  Normally we'd send it on behalf of a tenant unless we're
                    // pinging ourselves or the XManager
                    "Authorization: ".$authToken,
                    "X-XC-SCHEMA-VERSION: ".$version,
                    // Correct XFabric encoding
                    "Content-Type: ".$type,
                    // Destination Id.  We would normally grab this from a file, but we don't
                    // have one in this case
                    "X-XC-DESTINATION-ID: ".$destID,
                    // Length of message
                    "Content-Length: ".strlen($message)
                )
            );



            // Send the message
            $result = curl_exec($curl)."\n";


            // Check that it worked
            if(substr($result, 0, 15) == "HTTP/1.1 200 OK")
                $current .= "Ping successfully sent! \nMessage:\n".$message;
            else
                $current .= "Error in sending ping. \nResponse: ".$result;
        }
        catch (Exception $e)
        {
            // Oh no!  An exception!
            $current .= "Error in script: ".$e;
        }
    }
    catch (Exception $e)
    {
        // Oh, no!  An exception!
        $current .= "Message is not able to be avro-encoded!";
    }

    // Write to log file
    file_put_contents($file, $current);
}
?>