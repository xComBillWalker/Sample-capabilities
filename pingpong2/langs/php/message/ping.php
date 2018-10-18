<?php
// Sample php file to accept a ping from the XFabric and return a pong

// Get them variables
include_once('../variables.php');

// Open the log file
$current = file_get_contents($file);

// Get the message from the POST data
$vars = file_get_contents("php://input");

// Get the headers
$headers = getallheaders();
$current .= "\n<h2>Receiving Ping</h2> ".date('Y-m-d h:i:s');

// Some old code.  You can append this onto the above string to get
// the full headers and POST from the ping
//.var_export($headers, true)."\n POST: ".$vars."\n";

try
{
    // Check if the Tenant ID matches one of our capability's tenants
    if($headers['X-XC-TENANT-ID'] != $tenantPn)
    {
        throw new Exception('TenantID does not match any tenants');
    }
    else
    {
        $current .= "\nTenant ID matches a tenant of the capability";
    }

    // Check if its the XFabric sending us the message
    if($headers['Authorization'] != $xFabricAuth)
    {
        throw new Exception('Authorization does not match XFabric Bearer Token');
    }
    else
    {
        $current .= "\nAuthorization corresponds to the XFabric";
    }

    $current .= "\nPing successfully received!";
    $current .= "\nMessage:\n".$vars;


    // Respond with pong
    $current .= "\n<h2>Sending Pong</h2>";

    // Initiate cURL
    $curl = curl_init();


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


    // Craft cURL message

   // Get the message
    $message = $vars;

    // Address of XFabric with topic
    curl_setopt($curl, CURLOPT_URL, $fabricURL."/message/pong");

    // Set message as POST
    curl_setopt($curl, CURLOPT_POSTFIELDS, $message);

    // Set the headers
    curl_setopt($curl, CURLOPT_HTTPHEADER,
        array(
            // SELF Auth data.  Should check that the received Tenant ID corresponds to
            // one we have and respond with that
            "Authorization: ".$authToken,
            "X-XC-SCHEMA-VERSION: ".$version,
            // Correct XFabric encoding
            "Content-Type: ".$type,
            // Respond to the publisher that sent the message.  In this case, it's SELF
            "X-XC-DESTINATION-ID: ".print_r($headers["X-XC-PUBLISHER-ID"], true),
            // Length of message
            "Content-Length: ".strlen($message),
            // GUID of received message.  Allows XManager to better track the message flow
            "X-XC-MESSAGE-GUID-CONTINUATION: ".print_r($headers["X-XC-MESSAGE-GUID"], true)));


    // Send the message
    $current .= date('Y-m-d h:i:s');
    $result = curl_exec($curl);

    // Check that it worked
    if(substr($result, 0, 15) == "HTTP/1.1 200 OK")
        $current .= "\nPong sent successfully! \nMessage:\n".$message;
    else
        $current .= "\nError in sending pong. \nResponse: ".$result;
}
catch (Exception $e)
{
    // Oh no! An exception!
    $current .= "\nERROR: ".$e;
}

// Write to log file
file_put_contents($file, $current);

?>