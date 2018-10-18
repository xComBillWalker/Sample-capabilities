<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
    "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <title>Ping-pong</title>
</head>
<body>

<h1> Ping-pong Control Panel </h1>

<!-- set the POST that will call sendPong  -->
<form action="pingpong.php" method="post">
    <input type="hidden" name="pinged" value="true">
    <input type="submit" value="Send Ping">
</form>

<?php

// Script that sends the initial ping
require_once("pinger.php");

// Constant variables
require_once("variables.php");

// The message
// This will be encoded as an avro binary, but we can get away with
// any encoding as the XFabric will ignore the message and we are
// just sending something to our self
// Replace the below quote with your message or the empty string
$message = array("payload" => "Call me Ishmael");

// If the Send Ping button was clicked ...
if(isset($_POST["pinged"]))
{

    // ... send the ping
    if($_POST["pinged"] == "true")
    {
        sendPing($message);
        $_POST["pinged"] = "false";
    }

    // Set the POST that will check the log file
    echo
        "<form action=\"pingpong.php\" method=\"post\">
            <input type=\"hidden\" name=\"pinged\" value=\"false\">
            <input type=\"hidden\" name=\"checked\" value=\"true\">
            <input type=\"submit\" value=\"Check Status\">
        </form>
        <br />";

    // If the Check button was clicked ...
    if(isset($_POST["checked"]) && $_POST["checked"] == "true")
    {
        // ... read the log file ...
        $contents = file_get_contents($file);

        // ... and print an empty notice or the log
        if($contents == "")
        {
            echo "File is empty. <br \> No logs of pings or pongs";
        }
        else
        {
            echo nl2br($contents);
        }

        $_POST["checked"] = "false";
    }
}
?>
</body>
</html>