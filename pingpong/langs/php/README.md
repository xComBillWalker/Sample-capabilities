About
=====

This is the PHP version of the simple ping pong example. The goal is to demonstrate a basic messaging flow using the Fabric.


Running this example
====

1. Copy the entire php folder into your apache webroot into some folder say, "example"
2. Modify the .htaccess file to change the line 'RewriteBase /pingpong' to 'RewriteBase /example' or whatever name you choose.
3. Make sure the log file test.log is writable (chmod 655). Do a tail -f test.log in a new terminal window to keep the log file open. 
4. In your browser (assuming that apache is running), type "http://localhost/example/publish" in the address bar.
5. This action is mapped to the script 'publish_to_fabric.php', which parses the ping message schema from the ocl cloud https://api.x.com/ocl/message/ping and publishes a message on a topic called /message/ping. Please note that you have to register your capability, get your authorization tokens and change the fabric url to the appropriate sandbox address before running this action.
6. You will see two messages in the log file. The first will be a ping message sent my the fabric. This action is mapped to 'handle_ping.php' script. This responds with a pong action, which publishes to the logfile again.

