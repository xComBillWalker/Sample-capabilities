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
    "Bearer QGWSORnPqMs+4B4W4Nq54NfN12LWDGHUiSJ2GV6Szikef/c0LMQRthMF+sQnMuTqMrPs6QzW"  #fabric bearer token
  end
end

get '/' do
   "nope, not here. from client2, navigate to url /ping"
end



post '/message/ping' do
  puts "\nPing received on /message/ping\n-----\n"
  message_body = request.env["rack.input"].read
  headers = request_headers
  #puts "headers\n--------\n"
  #puts headers
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
    response = Fabric.post("/message/pong", \
    {:body => stringwriter.string, :headers => {'Content-Type' => 'avro/binary',  \
      'Authorization' => "Bearer EDgVoX1RU3ji6ms5f5+cbjnjeydbBFQGWWA/rngk3VWCiGjKRLqPX7lUo7wQqbi+karSEroD", \
      'X-XC-DESTINATION-ID' => publisher,\
      'X-XC-SCHEMA-VERSION' => "1.0.0"}})
    
  end
end

