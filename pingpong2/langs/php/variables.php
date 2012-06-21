<?php
// Various variables used in the php scripts
// Many of the variables will have to be changed to match your capability and XFabric

// Set to your preferred time zone
date_default_timezone_set('US/Central');

// The URI of the ping schema
$schema_uri = "https://api.x.com/ocl/message/ping/1.0.0";

// File where we will log out pings and pongs
// Set to where ever you'd like
$file = 'C:/XCOM/Magento/trunk/1.11/message/PingPongLog.txt';

// Version of the used schema
$version = "1.0.0";

// Encoding of message (not actually implemented in this example)
$type = "avro/binary";

// Address to send messages to the xFabric
// Set to your local version of the fabric or an online one
$fabricURL = "https://localhost:8080";

// XFabric Bearer Token
// Used to check that we're getting back messages from the XFabric
// Set to the XFabric Token given to your capability
$xFabricAuth = "Bearer y/64jZMGK2zpv+MQ9VDO6qrpSyXz6M7uRRL3n5yhJe3gOIBLAelEFceShwNukbhwpGAFOuCA";

// Your capability's SELF-Destination ID on the XFabric
// Set to the Destination ID given to your capability by the XFabric
// In other cases you would set this to the destination ID of the capability you wish to talk to
// (The XManager responds to pings directly if you want to ping it)
$destID = "l2RL2Xc4sOHsUCz23LPagSEFug6SOab87O36V/iBcp2qMHzLatLdcinxj3ZGUO0CONM8oRzl";

// The SELF-Authentication Token of your capability
// Set to the SELF-Authentication Token given to your capability by the XFabric
// In other cases you would use the Authentication Token of the Tenant you are sending/receiving the message on behalf of
$authToken = "Bearer yuN4t+HS5B5K6SFCZxb1A0dCVgH6qELfaUQDEgegjMqMupvUXPUwoZDYIV3bLi1JLHyGOi8H";

// The SELF-Tenant Pseudonym of your capability
// Set to the SELF-Tenant Pseudonym given to your capability by the XFabric
// In other cases you would use the Tenant Pseudonym of the Tenant you are sending/receiving the message on behalf of
$tenantPn = "+/9KdvQCgQvf2c/LDLyeKe0cFb1JNBA4r4D6sq7Pjtefe3BnDXmeLUyLs6v8VEW8WVYpnBuB"; // Tenant Pseudonym

?>