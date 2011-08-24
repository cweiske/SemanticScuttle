==================================
SemanticScuttle .phar installation
==================================

How to install the `.phar` version of SemanticScuttle.


Note: The ``.phar`` file can be used from the browser and
via command line. Use ``--help`` to find out what you can do.

.. contents::


Server setup
============
Apache or any other web server is by default not configured to let PHP handle
``.phar`` files, so we need to add it.

The default apache configuration on Debian contains the following lines::

    <FilesMatch "\.ph(p3?|tml)$">
        SetHandler application/x-httpd-php
    </FilesMatch>

which matches ``.php``, ``.php3`` and ``.phtml`` files.
Adding ``.phar`` is trivial::

    <FilesMatch "\.ph(ar|p3?|tml)$">

Restart your server after this configuration change.


Database setup
==============
Extract the database schema from the ``.phar`` file::

    $ php SemanticScuttle-0.98.3.phar x data/tables.sql tables.sql

Import the schema into you MySQL server::

    $ mysql -umyusername mydatabase < tables.sql


Database upgrades
-----------------
If you are upgrading from an earlier version of SemanticScuttle, you might need
to upgrade your database schema.
See the `upgrade instructions`_ for more information.

Get a list of all schema files with ::

    $ php SemanticScuttle-0.98.3.phar list | grep schema

and then extract the relevant ones with ::

    $ php SemanticScuttle-0.98.3.phar x data/schema/6.sql


.. _upgrade instructions: UPGRADE.html


Configuration
=============
Extract the configuration file from the ``.phar`` file::

    $ php SemanticScuttle-0.98.3.phar x data/config.php.dist SemanticScuttle-0.98.3.phar.config.php

Note that the file name must be exactly the name of the ``.phar``
plus ``.config.php`` - otherwise the configuration file is not detected.

After extracting it, modify it according to your needs.


Caching
-------
If you want to enable caching, make sure you use a ``$dir_cache`` that is
_outside_ the phar file.


Visit SemanticScuttle
=====================
Open your browser and navigate to the ``.phar`` file.

Happy bookmarking!
