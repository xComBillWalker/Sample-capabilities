About
=====

This is the PHP version of the simple ping pong example. The goal is to demonstrate a basic messaging flow using the Fabric.


Running this example
====

1. Copy the entire php folder into your apache webroot
2. Modify the .htaccess file to redirect "message/ping.php to "message/ping" and "message/pong.php" to "message/pong".
3. Make sure the log file "mesage/PingPongLog.txt" is writable (chmod 655).
4. Open "variables.php" and change the values to ones corresponding to your setup.  See the comments in the file for more details.
5. In your browser (assuming that apache is running), type "http://localhost/pingpong.php" in the address bar to access a webpage that can launch the initial ping and read the log file.
6. Alternatively you can change the file "pinger.php" to launch the initial ping when it is loaded (remove the function wrapping and define $message). You can then go to "http://localhost/pinger.php" to launch the ping.  You can then open the log file "message/PingPongLog.txt" to see the results.