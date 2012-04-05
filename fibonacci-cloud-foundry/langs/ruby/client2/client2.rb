# require 'sinatra'   # required for framework detection in cloud foundry.
require 'rubygems'
require 'bundler'
Bundler.require


class Fabric
  include HTTParty
  base_uri 'https://api.sandbox.x.com/fabric' 
end

helpers do
  def request_headers
    env.inject({}){|acc, (k,v)| acc[$1] = v if k =~ /^http_(.*)/i; acc}
  end
  
  def fabric_token
    "Bearer 39477dc4-4bfa-4a44-be77-8e649d513ac6"
    
  end
end

get '/' do
   "go to url/ping to start"
end

get '/ping' do
  
 destination_id = '4ccc670c-cc90-4cdb-804c-a12c7a11e2ed'
 authorization = 'Bearer 7c4cf104-911c-4a9c-b61b-9442ed3aac37'
 
  #get schema for ping
  file = HTTParty.get("https://api.x.com/ocl/message/ping/1.0.0")
  #ruby hackery before parsing the schema. ensures that avro lib cleanly parses this schema
  schema = Avro::Schema.parse(file.parsed_response.to_s.gsub(/\=\>/,':').gsub(/nil/,"\"null\""))
  #test message
  message = {"payload" => "0,1"}
  #into avro binary
  stringwriter = StringIO.new
  datumwriter = Avro::IO::DatumWriter.new(schema)
  encoder = Avro::IO::BinaryEncoder.new(stringwriter)
  datumwriter.write(message,encoder)  
  #send to fabric on topic /mesage/ping 
  #use self token as the authorization, destination is that of client1
  response = Fabric.post("/experimental/message/ping", \
  {:body => stringwriter.string, :headers => {'Content-Type' => 'avro/binary',  \
    'Authorization' => "#{authorization}", \
    'X-XC-DESTINATION-ID' => "#{destination_id}",\
    'X-XC-SCHEMA-URI' => "https://api.x.com/ocl/message/ping/1.0.0",\
    'X-XC-SCHEMA-VERSION' => "1.0.0"}})
  #pp response.inspect  
  "#{response.code}, #{response.headers.inspect}"
  
end


post '/experimental/message/pong' do
  puts "\nPong received on /message/pong\n-----\n"
  _authorization = 'Bearer 7c4cf104-911c-4a9c-b61b-9442ed3aac37'
  message_body = request.env["rack.input"].read
  headers = request_headers
  
  #verify that this is from fabric
  if fabric_token == request_headers["AUTHORIZATION"]
    #similar to above, send to pong
    file = HTTParty.get(headers["X_XC_SCHEMA_URI"])
    schema = Avro::Schema.parse(file.parsed_response.to_s.gsub(/\=\>/,':').gsub(/nil/,"\"null\""))
    stringreader = StringIO.new(message_body)
    decoder = Avro::IO::BinaryDecoder.new(stringreader)
    datumreader = Avro::IO::DatumReader.new(schema)
    read_value = datumreader.read(decoder)["payload"]
    puts "Fibonacci sequence\n---------\n"
    puts read_value
    puts "\n"
    #compute the next fibonacci
    arr = read_value.gsub(/\s+/,'').split(/,/)
    fib = arr.dup
    arr.push(Integer(fib.pop) + Integer(fib.pop))
    new_value = arr.join(',')
    message = {"payload" => "#{new_value}"}
    
    #encode the message
    stringwriter = StringIO.new
    datumwriter = Avro::IO::DatumWriter.new(schema)
    encoder = Avro::IO::BinaryEncoder.new(stringwriter)
    datumwriter.write(message,encoder)
    
   
    #publisher is X_XC_PUBLISHER_PSEUDONYM (new version 11.1) or X_XC_PUBLISHER (old version)
    if headers.has_key?("X_XC_PUBLISHER")
      publisher = headers["X_XC_PUBLISHER"]
    elsif 
      publisher = headers["X_XC_PUBLISHER_PSEUDONYM"]
    end
    #use self token as the authorization
    response = Fabric.post("/experimental/message/ping", \
    {:body => stringwriter.string, :headers => {'Content-Type' => 'avro/binary',  \
      'Authorization' => "#{_authorization}", \
      'X-XC-DESTINATION-ID' => publisher,\
      'X-XC-SCHEMA-URI' => "https://api.x.com/ocl/message/ping/1.0.0",\
      'X-XC-SCHEMA-VERSION' => "1.0.0"}})
    
  end
end