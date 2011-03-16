#/usr/bin/ruby

puts "|#{File.dirname(__FILE__)}/htmlpurified.php"

fh = Kernel.open("|#{File.dirname(__FILE__)}/htmlpurified.php", "w+")
document = File.open(ARGV[0], "r").read

fh.puts document
fh.close_write
output = fh.read
fh.close()

puts output