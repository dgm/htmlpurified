htmlpurified is a simple network echo service that uses
HTMLPurifier (http://htmlpurifier.org) to sanitize potentially
dangerous html messages, such as to display spam email in
Maia Mailguard (http://www.maiamailguard.com)

Copyright 2011 David Morton mortonda@dgrmm.net
Licensed under MIT, LGPL, and/or GPL licenses, whatever meets your needs and
matches the needs of HTMLPurifier, and the PEAR libraries.

In other words, it's open source.

INSTALL:
install prerequisites:

pear install Net_Server
pear install Console_CommandLine
pear channel-discover htmlpurifier.org
pear install hp/HTMLPurifier

then run it:

php htmlpurified.php


A simple ruby scrip is present to test it, with a local file as content:

ruby client.rb filename

NOTES:
For some reason the php side does not read the line endings and process
the final data, including the STOP command, unless you send enough data
to flush the buffer.  Hence an ugly loop to write 128 0's.  If anyone
has a solution to this, I'd love to hear. :)