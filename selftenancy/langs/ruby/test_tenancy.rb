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
    "Bearer z0d3WyWNb6MsrSh077FIs8vL0V2Yrt+Cy5hvkqw2+VJuITuL9H+MtavgA0BuCwcMdTe48Whl"  #fabric bearer token
  end
end

get '/' do
   "go to your url/test and look at your server log"
end

#Example uses self token to send messages
#Intent is to demonstrate self tenancy. That is listener has to have a relationship
#with the sender. 
#Here we are using the same sender and receiver, and they have an inherent relationship

get '/test' do
  #get schema for ping
   file = HTTParty.get("https://api.x.com/ocl/marketplace/profile/get/1.0.0")
   #ruby hackery before parsing the schema. ensures that avro lib cleanly parses this schema
   schema = Avro::Schema.parse(file.parsed_response.to_s.gsub(/\=\>/,':').gsub(/nil/,"\"null\""))
   #test message
   message = {"xProfileId" => "123"}
   #into avro binary
   stringwriter = StringIO.new
   datumwriter = Avro::IO::DatumWriter.new(schema)
   encoder = Avro::IO::BinaryEncoder.new(stringwriter)
   datumwriter.write(message,encoder) 
    
   #send to fabric using self token
   response = Fabric.post("/marketplace/profile/get", \
   {:body => stringwriter.string, :headers => {'Content-Type' => 'avro/binary',  \
     'Authorization' => "Bearer Zn9EgRFGJ6Wb8YWjIAadLUqgpk0lW7jA7RcGLKaBEd9tdPn7GXu/Q2bNKXGeKpPcgtEv4Pzo", \
     'X-XC-SCHEMA-VERSION' => "1.0.0"}})

   "#{response.code}, #{response.headers.inspect}"
end


post '/marketplace/profile/get' do
  puts "\nmessage received on /marketplace/profile/get\n-----\n"
  message_body = request.env["rack.input"].read
  headers = request_headers
  puts "headers\n--------\n"
  puts headers
  #verify that this is from fabric
end

