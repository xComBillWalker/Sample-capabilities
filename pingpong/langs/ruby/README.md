Ruby Example
====

This example demonstrates a ping pong message using Ruby. Sinatra is used as the wesbserver. Before running this example -
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

	1. Navigate to /ping
	2. The logs will show the message being sent to the fabric at message/ping, which gets routed back to your application
	3. The message will be bounced back to /message/pong, which again gets routed to your application
	
