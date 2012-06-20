<?php
// Sample php file to accept a pong from the XFabric

// Get those variables
include_once('../variables.php');

// Get Avro
include_once('avro.php');

// Open the log file
$current = file_get_contents($file);

// Append the pong to the log
// Get the message from the POST data
$vars = file_get_contents("php://input");

// Get the headers
$headers = getallheaders();

// Throw out an html header
$current .= "\n<h2>Receiving Pong</h2> ".date('Y-m-d h:i:s');

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

    $current .= "\nPong successfully received!";
    $current .= "\nMessage:\n".$vars;
}
catch (Exception $e)
{
    // Oh no!  An exception!
    $current .= "\nERROR: ".$e;
}

// Write to the log
file_put_contents($file, $current);


?>