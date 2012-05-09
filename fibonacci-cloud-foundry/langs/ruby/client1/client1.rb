# require 'sinatra'   # required for framework detection in cloud foundry.
require 'rubygems'
require 'bundler'
Bundler.require

class Fabric
  include HTTParty
  #base_uri 'https://api.sandbox.x.com/fabric' 
  base_uri 'https://api.sandbox.x.com:444/fabric' 
end

helpers do
  def request_headers
    env.inject({}){|acc, (k,v)| acc[$1] = v if k =~ /^http_(.*)/i; acc}
  end
  
  def fabric_token
    "Bearer d67OZUVRi1PwMw6CfPhnB7jVPgcZPJF8BY1uCsrp6GvK8266xPEcHjSl9rKVtCWB0Oe4OIr3"
  end
end

get '/' do
   "nope, not here. from client2, navigate to url /ping"
end



post '/message/ping' do
  _authorization = "Bearer gIvYcyvlRnZlZeESGN5w/XL29kz7X7jaONmRcaZu0f9NBGo7hiHgspTxHUzGrcwdpQehV5w7"
  puts "\nPing received on /message/ping\n-----\n"
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
    
    #stop after 10 numbers
    if arr.size < 10
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
      response = Fabric.post("/message/pong", \
      {:body => stringwriter.string, :headers => {'Content-Type' => 'avro/binary',  \
        'Authorization' => "#{_authorization}", \
        'X-XC-DESTINATION-ID' => publisher,\
        'X-XC-SCHEMA-URI' => "https://api.x.com/ocl/message/pong/1.0.0",\
        'X-XC-SCHEMA-VERSION' => "1.0.0"}})
    end
    
  end
end

