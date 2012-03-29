Ruby Example
====

This example demonstrates how a capability can use the self token to send a message. Remember that a relationship is needed between the sender and the receiver. When you are using self-tenancy tokens, you should ensure that listener authorizes the sender capability. Here, I am demonstrating using a single capability and different routes. Sinatra is used as the wesbserver. Before running this example -

1. gem install sinatra
2. gem install httparty
3. gem install avro

You need to update the bearer tokens to reflect your credentials. To run this example, type

	sinatra pingpong.rb
	
If you want to modify the code and look at changes immediately during development without having to restart the server everytime, install the 
awesome shotgun gem
	gem install shotgun
	shotgun pingpong.rb
	
Update the capability endpoint based on whatever your webserver's public ip address. 

	1. Navigate to /test
	2. The logs will show the message being sent to the fabric at /marketplace/profile/get, which gets routed back to your application
	3. The important thing here is the use of self token to send messages
	
