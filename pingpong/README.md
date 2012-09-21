PingPong Demo
=============

The PingPong demo demonstrates the basic messaging mechanism the X.commerce Fabric uses. 

In this demo, a capability posts a Ping message to the Fabric on topic /com.x.ecosystemmanagement.v2/PingPong/Ping. The receiving capability receives the Ping message, 
handles it (for example, writes to a log file) and responds (via the Fabric) with a Pong message on topic /com.x.ecosystemmanagement.v2/PingPong/Pong.

The publishing capability puts the receiving capability's Capability ID in the X-XC-DESTINATION-ID header for the Ping message, so the Fabric delivers the Ping message
to just this capability. So the Fabric sends the Pong message to the capability that originally sent the Ping message, the receiver sets the X-XC-DESTINATION-ID header 
of the Pong message to the value of the X-XC-PUBLISHER-PSEUDONYM header in the Ping message.

For more information, see the README file in the language-specific folder.


