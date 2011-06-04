Rules for developers
====================

1. Coding style
---------------
SemanticScuttle uses the PEAR Coding Standards.
While quite some parts still do not follow them, all of the
code will be coverted to them. When developing new code,
adhere to it.

A helpful tool to check your coding style is PHP CodeSniffer,
http://pear.php.net/package/PHP_CodeSniffer


2. Unit tests
-------------
At least the service and model classes have unit tests.
If you fix things in there, make sure you
a) do not break the tests or
b) fix the tests if the old behavior was broken


3. Keep security in mind
------------------------
As a web application, there are several attack vectors to SemanticScuttle.
When processing user input (form variables, URL parameters)
be sure to convert and validate them. If you expect a bookmark id,
there is no reason not to cast the variable to (int).

Filter input, escape output.
