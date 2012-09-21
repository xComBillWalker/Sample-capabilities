About
=====

This is the PHP version of the PingPong demo. This program's goal is to demonstrate a basic message flow using the Fabric.


Running this example
====================

1. Copy the entire php folder into your Apache webroot, to some folder, such as "example"
2. In the .htaccess file, change the line 'RewriteBase /web/pingpong' to 'RewriteBase /example' or whatever name you choose.
3. Make sure the log file ping_pong.log is writable (chmod 655). Do a tail -f ping_pong.log in a new terminal window to keep the log file open. 
4. In your browser (assuming that Apache is running), type "http://localhost/example/publish" in the address bar.
5. This action is mapped to the script 'send_ping.php', which retrieves the Ping message schema from the OCL server, uses it to Avro-encode the Ping message 
   and publishes this message on a topic /com.x.ecosystemmanagement.v2/PingPong/Ping. Please note that you must register a sending capability (named, for example, Pinger) 
   and a receiving capability (named, for example, Ponger) to generate your authorization credentials. You must then add these credentials to the file common.php.
6. After running the demo, open the log file. It shows you what happens as the Ping message is received by Ponger and as the Pong response message is received by Ponger.

