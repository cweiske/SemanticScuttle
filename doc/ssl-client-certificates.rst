=======================
SSL client certificates
=======================

By using SSL client certificates, you get automatically logged into
SemanticScuttle without using a password or submitting a login form.

Any number of certificates can be registered with a user account,
making it easy to login to the same installation from home and from
work - without risking to use the same certificate on both machines.


Usage scenarios
===============
The scenarios assume that the web server is configured_ properly
to request client certificates.

.. _configured: `Configuring your web server`_


Registering a certificate with an existing account
--------------------------------------------------
You already have an account and want to associate a SSL client certificate
with it.

1. Visit your profile page
2. Click "Register current certificate to automatically login."

That's it. Now logout and click "Home". You will be logged in again
automatically.


Registering a certificate with a new account
--------------------------------------------
When you do not have an user account yet, just visit the registration
page. Your email address will already be filled in, using the information
from the SSL certificate.

Provide the rest of the data and submit the form.
The certificate will automatically be associated to your account,
and the user name will also be set for you.



Configuring your web server
===========================

Getting SSL certificates
------------------------
You need both a server certificate for normal HTTPS mode, as well as a client
certificate in your browser.

CAcert.org is a good place to obtain both.
You are of course free to generate your own certificate with i.e. openssl
or buy a certificate from another certificate authority, but this is out
of this document's scope.

Server certificate
''''''''''''''''''
First, generate a Certificate Signing Request with the `CSR generator`__.
Store the key file under ::

  /etc/ssl/private/bookmarks.cweiske.de.key

Use the the .csr file and the CAcert web interface to generate a signed
certificate. Store it as ::

  /etc/ssl/private/bookmarks.cweiske.de-cacert.pem

Now fetch both official CAcert certificates (root and class 3) and put both
together into ::

  /etc/ssl/private/cacert-1and3.crt

.. _CSR: http://wiki.cacert.org/CSRGenerator
__ CSR_


Client certificate
''''''''''''''''''
CAcert also offers to create client certificates. You do not need a
certificate sign request but just can create it on their web page.

After creation, you can simply install it in your browser by clicking
on the appropriate link on the CAcert page.

Once you got the certificate installed in your browser, you can transfer
it to other browsers by exporting it in the `PKCS #12` format
(with private key included) and importing it in any other browsers
you use.



Apache configuration
--------------------
To make use of SSL client certificates, you need to deliver SemanticScuttle
via HTTPS.

Note that you can equip several virtual hosts with SSL certificates
and keep them on the same, standard SSL port by using SNI -
`Server Name Indication`__.

.. _SNI: http://wiki.apache.org/httpd/NameBasedSSLVHostsWithSNI
__ SNI_

A basic virtual host configuration with SSL looks like this:

:: 

  <VirtualHost *:443>
      ServerName bookmarks.cweiske.de

      LogFormat "%V %h %l %u %t \"%r\" %s %b" vcommon
      CustomLog /var/log/apache2/access_log vcommon

      VirtualDocumentRoot /home/cweiske/Dev/html/hosts/bookmarks.cweiske.de
      <Directory "/home/cweiske/Dev/html/hosts/bookmarks.cweiske.de">
          AllowOverride all
      </Directory>

      SSLEngine On
      SSLCertificateFile /etc/ssl/private/bookmarks.cweiske.de-cacert.pem
      SSLCertificateKeyFile /etc/ssl/private/bookmarks.cweiske.de.key

      SSLCACertificateFile /etc/ssl/private/cacert-1and3.crt
  </VirtualHost>

Apart from that, you might need to enable the SSL module in your webserver,
i.e. by executing ::

  $ a2enmod ssl


Now that SSL works, you can configure your web server to request client
certificates.

:: 

      ...
      </Directory>

      SSLVerifyClient optional
      SSLVerifyDepth 1
      SSLOptions +StdEnvVars
  </VirtualHost>

There are several options you need to set:


``SSLVerifyClient optional``
  You may choose ``optional`` or ``required`` here.
  ``optional`` asks the browser for a client certificate but accepts
  if the browser (the user) does choose not to send any certificate.
  This is the best option if you want to be able to login with and
  without a certificate.

  Setting ``required`` makes the web server terminate the connection
  when no client certificate is sent by the browser.
  This option may be used when all users have their client certificate set.

``SSLVerifyDepth 1``
  Your client certificate is signed by a certificate authority (CA),
  and your web server trusts the CA specified in ``SSLCACertificateFile``.
  CA certificates itself may be signed by another authority, i.e. like ::

    CAcert >> your own CA >> your client certificate

  In this case, you have a higher depth. For most cases, 1 is enough. 

``SSLOptions +StdEnvVars``
  This makes your web server pass the SSL environment variables to PHP,
  so that SemanticScuttle can detect that a client certificate is available
  and read its data.

  In case you need the complete certificate
  \- which SemanticScuttle does *not* need - you have to add ``+ExportCertData``
  to the line.


That's it. Restart your web server and visit your SemanticScuttle installation.
Continue reading the `Usage scenarios`_.
