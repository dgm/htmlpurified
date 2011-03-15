#/usr/bin/ruby
require 'socket'      # Sockets are in standard library

hostname = 'localhost'
port = 6242

streamSock = TCPSocket::new( hostname, port )
File.open(ARGV[0], "r").each_line do |line|
  streamSock.puts( line )
end
streamSock.puts("STOP")


#kludge to get php's Net_Server to finish the connection
128.times do
  streamSock.write(0)
end


response = ""
while str = streamSock.gets
  response += str
end
streamSock.close

puts response