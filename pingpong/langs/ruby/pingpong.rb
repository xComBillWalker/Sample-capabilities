require 'rubygems'
require 'sinatra'
require 'httparty'
require 'avro'
require 'pp'


class Fabric
  include HTTParty
  base_uri 'https://localhost:8080' 
end

helpers do
  def request_headers
    env.inject({}){|acc, (k,v)| acc[$1] = v if k =~ /^http_(.*)/i; acc}
  end
  
  def fabric_token
    #change this to your capability specific fabric bearer token
    "Bearer 1kFKrtMpvm1KPdetDV/9PvQxTgzJXtkxYgGuodJir5yajHwegtCbJOoY+zvKDDh8oLxIiA0X"  #fabric bearer token
  end
end

get '/' do
   "go to your url/ping"
end

get '/ping' do
  
  #get schema for ping
  file = HTTParty.get("https://api.x.com/ocl/message/ping/1.0.0")
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
  response = Fabric.post("/message/ping", \
  {:body => stringwriter.string, :headers => {'Content-Type' => 'avro/binary',  \
    'Authorization' => "Bearer jS2t0mxpNWtXsoMQtcoyw2NtyDZDz+aHjuIb+z1PtEiqmPXkzEoWJH4NnBuL5MHWXI1WHyWc", \
    'X-XC-DESTINATION-ID' => '24+4FfGxHYE+KsK6IVFMU0F7wLUYc+hy3mIGC92zF8eC7raTQ3pa3l6L3IE/PuaV92gz4eZc',\
    'X-XC-SCHEMA-VERSION' => "1.0.0"}})
    
  "#{response.code}, #{response.headers.inspect}"
  
end

post '/message/ping' do
  puts "\nPing received on /message/ping\n-----\n"
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
    
    #publisher is X_XC_PUBLISHER_PSEUDONYM (new version 11.1) or X_XC_PUBLISHER (old version)
    if headers.has_key?("X_XC_PUBLISHER")
      publisher = headers["X_XC_PUBLISHER"]
    elsif 
      publisher = headers["X_XC_PUBLISHER_PSEUDONYM"]
    end
    response = Fabric.post("/message/pong", \
    {:body => message_body, :headers => {'Content-Type' => 'avro/binary',  \
      'Authorization' => "Bearer jS2t0mxpNWtXsoMQtcoyw2NtyDZDz+aHjuIb+z1PtEiqmPXkzEoWJH4NnBuL5MHWXI1WHyWc", \
      'X-XC-DESTINATION-ID' => publisher,\
      'X-XC-SCHEMA-VERSION' => "1.0.0"}})
    
  end
end

post '/message/pong' do
  puts "\nPing received on /message/pong\n-----\n"
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
  end
end