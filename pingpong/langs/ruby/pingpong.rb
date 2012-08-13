require 'rubygems'
require 'sinatra'
require 'httparty'
require 'avro'
require 'pp'
require 'ostruct'
#require 'uuidtools'
require 'uuid'



class Fabric
  include HTTParty
  base_uri 'https://api.sandbox.x.com/fabric'
end

class Message
  def pingpong
    {"schema_uri_ping" => 'https://api.x.com/ocl/com.x.ecosystemmanagement.v1/PingPong/Ping/1.0.6',
    "schema_uri_pong" => 'https://api.x.com/ocl/com.x.ecosystemmanagement.v1/PingPong/Pong/1.0.6',
    "schema_ver_ping" => '1.0.6',
    "schema_ver_pong" => '1.0.6',
    "schema_ver_trans_completed" => '1.0.1'}
  end
end

helpers do
  def request_headers
    env.inject({}){|acc, (k,v)| acc[$1] = v if k =~ /^http_(.*)/i; acc}
  end
  
  def fabric_token
    #fib1-x capability
    #change this to your capability specific fabric bearer token
    "Bearer lq7P9IkRzn6MfXdJ20VcI6NhppyPb6+bcKxvMiIFXZNzfBDn7ufYoXi5BWaMqe9y+5GoNQ5g"  #fabric bearer token
  end
  
  def my_authorization
    #SV test merchant
    "Bearer bOQPCZn0Z7WDQ0JwgcEd1lH+DQbrHh8x1dBmEcw7X2rGqIgPOZGGP96hb0GLqZFHTP1XX5z/"
  end
  
  def my_destination_id
    "g8T4114vhIYNqlxu3jA+geDoRh0/RzEJI6kM1Y4hEU6EcCUrs5nM4raSxiWRYiHheTpnDPhI"
  end
end

get '/' do
   "go to your url/ping"
end

get '/ping' do
  
  ping = OpenStruct.new Message.new.pingpong
  #get schema for ping
  file = HTTParty.get(ping.schema_uri_ping)
  #ruby hackery before parsing the schema. ensures that avro lib cleanly parses this schema
  schema = Avro::Schema.parse(file.parsed_response.to_s.gsub(/\=\>/,':').gsub(/nil/,"\"null\""))
  #test message
  message = {"payload" => "test"}
  #into avro binary
  stringwriter = StringIO.new
  datumwriter = Avro::IO::DatumWriter.new(schema)
  encoder = Avro::IO::BinaryEncoder.new(stringwriter)
  datumwriter.write(message,encoder)  
  
  #send to fabric on topic /mesage/ping 
  response = Fabric.post("/com.x.ecosystemmanagement.v1/PingPong/Ping", \
  {:body => stringwriter.string, :headers => {'Content-Type' => 'avro/binary',  \
    'Authorization' => my_authorization, \
    'X-XC-DESTINATION-ID' => my_destination_id,\
    'X-XC-MESSAGE-GUID-CONTINUATION' => '',\
    'X-XC-WORKFLOW-ID' => UUID.new.generate,\
    'X-XC-TRANSACTION-ID' => UUID.new.generate,\
    'X-XC-SCHEMA-VERSION' => ping.schema_ver_ping}})
    
  "#{response.code}, #{response.headers.inspect}"
  
end

post '/com.x.ecosystemmanagement.v1/PingPong/Ping' do
  puts "\nPing received on /com.x.ecosystemmanagement.v1/PingPong/Ping/\n-----\n"
  pong = OpenStruct.new Message.new.pingpong
  message_body = request.env["rack.input"].read
  headers = request_headers
  puts "headers\n--------\n"
  puts headers
  #verify that this is from fabric
  if fabric_token == request_headers["AUTHORIZATION"]
    #similar to above, send to pong
    #for simplicity let us just respond the same message to the publisher
    #in other cases we can get the schema from X_XC_SCHEMA_URI header and do some processing
    # and send something back. the intent here is just to pong back!
    
    #publisher is X_XC_PUBLISHER_PSEUDONYM 
    publisher = headers["X_XC_PUBLISHER_PSEUDONYM"]
    puts "publisher #{publisher}"
    response = Fabric.post("/com.x.ecosystemmanagement.v1/PingPong/Pong", \
    {:body => message_body, :headers => {'Content-Type' => 'avro/binary',  \
      'Authorization' => my_authorization, \
      'X-XC-DESTINATION-ID' => publisher,\
      'X-XC-MESSAGE-GUID-CONTINUATION' => headers["X_XC_MESSAGE_GUID"],\
      'X-XC-TRANSACTION-ID' => headers['X_XC_TRANSACTION_ID'],\
      'X-XC-WORKFLOW-ID'=> headers['X_XC_WORKFLOW_ID'],\
      'X-XC-SCHEMA-VERSION' => pong.schema_ver_pong}})
  else
    #auth failed. terminate.
    puts "FATAL: Authorization failure."
    #exit
  end
end

post '/com.x.core.v1/TransactionCompleted' do
  puts "Received transaction completed message on /com.x.core.v1/TransactionCompleted"
end

post '/com.x.ecosystemmanagement.v1/PingPong/Pong' do
  pong = OpenStruct.new Message.new.pingpong
  puts "\nPing received on /com.x.ecosystemmanagement.v1/PingPong/Pong/\n-----\n"
  message_body = request.env["rack.input"].read
  headers = request_headers
  puts "headers\n--------\n"
  puts headers
  
  #now let us fetch the schema and deserialize
  #verify the request is from the fabric
  if fabric_token == request_headers["AUTHORIZATION"]
    file = HTTParty.get(headers["X_XC_SCHEMA_URI"])
    schema = Avro::Schema.parse(file.parsed_response.to_s.gsub(/\=\>/,':').gsub(/nil/,"\"null\""))
    stringreader = StringIO.new(message_body)
    decoder = Avro::IO::BinaryDecoder.new(stringreader)
    datumreader = Avro::IO::DatumReader.new(schema)
    
    #read the message
    read_value = datumreader.read(decoder)
    puts "\nmessage\n-------\n"
    puts read_value
    puts "\n replying with a transaction completed mesage on /com.x.core.v1/TransactionCompleted"
    publisher = headers["X_XC_PUBLISHER_PSEUDONYM"]
    response = Fabric.post("/com.x.core.v1/TransactionCompleted", \
    {:body => message_body, :headers => {'Content-Type' => 'avro/binary',  \
      'Authorization' => my_authorization, \
      'X-XC-DESTINATION-ID' => publisher,\
      'X-XC-MESSAGE-GUID-CONTINUATION' => headers["X_XC_MESSAGE_GUID"],\
      'X-XC-TRANSACTION-ID' => headers['X_XC_TRANSACTION_ID'],\
      'X-XC-WORKFLOW-ID'=> headers['X_XC_WORKFLOW_ID'],\
      'X-XC-SCHEMA-VERSION' => pong.schema_ver_trans_completed}})
  else
    puts "FATAL: Authorization failure."
    #exit
  end
end