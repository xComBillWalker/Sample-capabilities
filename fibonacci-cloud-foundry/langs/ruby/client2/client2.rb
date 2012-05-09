# require 'sinatra'   # required for framework detection in cloud foundry.
require 'rubygems'
require 'bundler'
Bundler.require


class Fabric
  include HTTParty
  base_uri 'https://api.sandbox.x.com:444/fabric' 
end

helpers do
  def request_headers
    env.inject({}){|acc, (k,v)| acc[$1] = v if k =~ /^http_(.*)/i; acc}
  end
  
  def fabric_token
    "Bearer r9sOhMSSMMbdeWFf5fzJSQZwi8uLKwYuX980C4mhb7RWNyE4DG5kfLEBSDEldx816PMj2eqE"
    
  end
end

get '/' do
   "go to url/ping to start"
end

get '/ping' do
  
 destination_id = '1hY9DuEYLolAeCodxGvaxc+8I2NtqUWEPG14Uc4JFzveKav4mwua2IXH2YzyXWf4DLanA4r8'
 authorization = 'Bearer A5m8SBG9+HE8tuFxzN1ndSo7yzmILjPty/q5v/Wxpro7BhYHxi2qD16suYPAIzk+D6kPeHMa'
 
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
  response = Fabric.post("/message/ping", \
  {:body => stringwriter.string, :headers => {'Content-Type' => 'avro/binary',  \
    'Authorization' => "#{authorization}", \
    'X-XC-DESTINATION-ID' => "#{destination_id}",\
    'X-XC-SCHEMA-URI' => "https://api.x.com/ocl/message/ping/1.0.0",\
    'X-XC-SCHEMA-VERSION' => "1.0.0"}})
  #pp response.inspect  
  "#{response.code}, #{response.headers.inspect}"
  
end


post '/message/pong' do
  puts "\nPong received on /message/pong\n-----\n"
  _authorization = 'Bearer A5m8SBG9+HE8tuFxzN1ndSo7yzmILjPty/q5v/Wxpro7BhYHxi2qD16suYPAIzk+D6kPeHMa'
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
    response = Fabric.post("/message/ping", \
    {:body => stringwriter.string, :headers => {'Content-Type' => 'avro/binary',  \
      'Authorization' => "#{_authorization}", \
      'X-XC-DESTINATION-ID' => publisher,\
      'X-XC-SCHEMA-URI' => "https://api.x.com/ocl/message/ping/1.0.0",\
      'X-XC-SCHEMA-VERSION' => "1.0.0"}})
    
  end
end