htmlpurified is a simple php script that uses
HTMLPurifier (http://htmlpurifier.org) to sanitize potentially
dangerous html messages, such as to display spam email in
Maia Mailguard (http://www.maiamailguard.com)

Copyright 2011 David Morton mortonda@dgrmm.net
Licensed under MIT, LGPL, and/or GPL licenses, whatever meets your needs and
matches the needs of HTMLPurifier, and the PEAR libraries.

In other words, it's open source.

INSTALL:
install prerequisites:

pear channel-discover htmlpurifier.org
pear install hp/HTMLPurifier

then run it:

cat file | php htmlpurified.php


A simple ruby script is present to test it, with a local file as content:

ruby client.rb filename

NOTES:
Removed the network server idea, a simple piped program is more than sufficient.
