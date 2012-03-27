Ping Pong Example
=============

This example demonstrates a basic messaging mechanism using the Fabric. A capability posts to /message/ping on the Fabric. The receiving capability (which is the same publishing capability in this case) receives the ping message, handles it (like writing to a log file) and responds with a pong message. The publishing capability uses its own destination ID for the X-XC-DESTINATION-ID header value for the ping message and in the code that shows the receiving example, it retrieves the destination of the sender by using X-XC-PUBLISHER-PSEUDONYM header.

You can find more details in the language specific folder.


