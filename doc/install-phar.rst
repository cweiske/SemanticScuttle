==================================
SemanticScuttle .phar installation
==================================

How to install the `.phar` version of SemanticScuttle.


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


2. Database setup
3. Configuration
   1. Configuration file
   2. Caching
4. Visit SemanticScuttle
