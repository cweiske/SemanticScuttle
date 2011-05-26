============================================
External authentication with SemanticScuttle
============================================

Most times, one piece of software is only a part in the big puzzle
that makes the software landscape of a company or organization.
SemanticScuttle is not different and should integrate as nicely as
possible with all other systems.

One of the basic tasks of integration is user authentication against
a central database - be it a central user database, an LDAP or a
active directory server.

Since version 0.96, SemanticScuttle supports user authentication against
external systems. To provide a wide range of supported systems, we chose
to utilize PEAR's `Authentication package`__.
It does this by providing different "`authentication containers`__",
for example Database, IMAP, LDAP, POP3, RADIUS, SAP and SOAP.

Please be aware of the fact that, after successful authentication, the user
and his scrambled password are stored in the SemanticScuttle database.
This is required for proper functioning of the software. It does not mean
that you will be able to login if your external authentication provider
is offline - you won't, execpt you switch it off in the SemanticScuttle
configuration.


__ http://pear.php.net/package/Auth
__ http://pear.php.net/manual/en/package.authentication.auth.intro-storage.php


Basic configuration
===================
The default configuration file ``data/config.default.php`` has an own section
on auth options and an explanation of the single entries.

To utilize the external authentication, you need to install the
PEAR Auth package: ::

  $ pear install auth

If you do not have a PEAR installation available, you can try to manually
install the files in the src/ directory. If you choose to do that, the
src/ directory should look similar to that: ::

  src/
   Auth.php
   Auth/
    Anonymous.php
    Container.php
    Container/
    ..
   SemanticScuttle/
    header.php
    ..

After that, modify your ``data/config.php`` file. The most important change
is to use ::

  $serviceoverrides['User'] = 'SemanticScuttle_Service_AuthUser';

which tells SemanticScuttle to switch to the special authentication service.

Now that's done, you can configure the single auth options:

``$authType = 'MDB2';``
  selects the authentication container.

``$authOptions``
  is an array of options specific to the authentication container. Please
  consult the PEAR Auth documentation for more information.

``$authDebug = true;``
  should be used when setup fails, since it may give important hints
  where it fails.

  Please note that login will seem to fail with
  debugging activated. Going back to the main page after that will
  show that you are logged in.



Authentication examples
=======================

General database authentification
---------------------------------
Here you also need the PEAR `MDB2 package`_.
The "``new_link``" option is important!

``config.php`` settings: ::

  $serviceoverrides['User'] = 'SemanticScuttle_Service_AuthUser';
  $authType = 'MDB2';
  $authOptions = array(
      'dsn' => array(
          'phptype'  => 'mysql',
          'hostspec' => 'FIXME',
          'username' => 'FIXME',
          'password' => 'FIXME',
          'database' => 'FIXME',
          'new_link' => true,
      ),
      'table'       => 'usersFIXME',
      'usernamecol' => 'usernameFIXME',
      'passwordcol' => 'passwordFIXME',
      'cryptType'   => 'md5',
  );


Mantis Bugtracker
-----------------
Here you also need the PEAR `MDB2 package`_.

``config.php`` settings: ::

  $serviceoverrides['User'] = 'SemanticScuttle_Service_AuthUser';
  $authType = 'MDB2';
  $authOptions = array(
      'dsn' => array(
          'phptype'  => 'mysql',
          'hostspec' => 'FIXME',
          'username' => 'FIXME',
          'password' => 'FIXME',
          'database' => 'FIXME',
          'new_link' => true,
      ),
      'table'       => 'mantis_user_table',
      'usernamecol' => 'username',
      'passwordcol' => 'password',
      'cryptType'   => 'md5',
  );

.. _MDB2 package: http://pear.php.net/package/MDB2


MediaWiki
---------
Unfortunately, the password column does not contain a simple hashed
password - for good reasons as described on
http://www.mediawiki.org/wiki/Manual_talk:User_table#user_password_column

If you configure your MediaWiki_ to use passwords without salt, you
can make it work nevertheless:

MediaWiki ``LocalSettings.php``: ::

  $wgPasswordSalt = false;

\- after that, users need to change/update their passwords to get them
unsalted in the database. You can verify if the passwords are unhashed
if you do ::

  SELECT CAST( user_password AS CHAR ) FROM user

on your MediaWiki database. Passwords prefixed with "``:A:``" can be used.

Another problem is that mediawiki user names begin with an uppercase letter.
You need to modify ``www/login.php`` and remove the "``utf8_strtolower``" function
call: ::

  $posteduser = trim(utf8_strtolower(POST_USERNAME));

becomes ::

  $posteduser = trim(POST_USERNAME);


``config.php`` settings: ::

  $serviceoverrides['User'] = 'SemanticScuttle_Service_AuthUser';
  $authType = 'MDB2';
  $authOptions = array(
      'dsn' => array(
          'phptype'  => 'mysql',
          'hostspec' => 'FIXME',
          'username' => 'FIXME',
          'password' => 'FIXME',
          'database' => 'FIXME',
          'new_link' => true,
      ),
      'table'       => 'user',
      'usernamecol' => 'user_name',
      'passwordcol' => 'user_password',
      'cryptType'   => 'md5_mediawiki',
  );
  function md5_mediawiki($password) {
      return ':A:' . md5($password);
  }


.. _MediaWiki: http://www.mediawiki.org/wiki/MediaWiki

Active Directory / LDAP
-----------------------
Here we authenticate against an active directory server.

``config.php`` settings: ::

  $serviceoverrides['User'] = 'SemanticScuttle_Service_AuthUser';
  $authType = 'LDAP';
  $authOptions = array(
      'host'     => '192.168.1.4',
      'version'  => 3,
      'basedn'   => 'DC=EXAMPLE,DC=ORG',
      'binddn'   => 'readuser',
      'bindpw'   => 'readuser',
      'userattr' => 'sAMAccountName',
      'userfilter' => '(objectClass=user)',
      'attributes' => array(''),
  );
  $authEmailSuffix = '@example.org';

