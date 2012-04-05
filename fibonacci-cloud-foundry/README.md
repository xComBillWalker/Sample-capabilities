
I bet we all can write a Fibonacci code in 4 lines. How about, computing a fibonacci series over the Fabric. I feel it is a great example that demonstrates the fundamental principles regarding tenancy, authorization, messaging, etc. Here is the situation - There are two webservers that are registered capabilities. We initiate the computation by pinging an url from one of the clients. After that, the messages autonomously go back and forth between the two capabilities using /message/ping and /message/pong. Each capability deserializes the message, adds the next number in the fibonacci sequence and sends the data back in Avro/binary. This goes on and on..and on.


