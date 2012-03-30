Ruby Example
====

I bet we all can write a Fibonacci code in 4 lines. How about, computing a fibonacci series over the Fabric. I feel it is a great example that demonstrates the fundamental principles regarding tenancy, authorization, messaging, etc. Here is the situation - There are two webservers that are registered capabilities. We initiate the computation by pinging an url from one of the clients. After that, the messages autonomously go back and forth between the two capabilities using /message/ping and /message/pong. Each capability deserializes the message, adds the next number in the fibonacci sequence and sends the data back in Avro/binary. This goes on and on..and on.


1. gem install sinatra
2. gem install httparty
3. gem install avro

You need to update the bearer tokens to reflect your credentials. To run this example, type

	sinatra client1.rb
	
If you want to modify the code and look at changes immediately during development without having to restart the server every time, install the awesome shotgun gem
	gem install shotgun
	shotgun client1.rb -p 9393
	shotgun client2.rb -p 9494
	
Update the capability endpoint based on whatever your webserver's public ip address. 

	1. Navigate to /ping on client2
	2. The logs will show the message being sent to the fabric at message/ping which is defined in client1. The fibonacci sequence will be printed.
	3. The message will be sent with updated sequencde  to /message/pong, which is defined in client2.
	
