===================
Configuration files
===================

SemanticScuttle uses at least two configuration files:

1. Default configuration file ``config.default.php``
2. Custom configuration file ``config.php``


The **default configuration** file contains sensible defaults for most users
that do not need to be changed to get started.

Never change it - it will get overwritten with the next update.
If you want to change values in it, copy them into your personal
``config.php`` file - updates to SemanticScuttle will not change that one.

The **custom configuration** file, ``config.php`` is created by copying the
shipped ``config.php.dist`` file and modifying the values in there.

It consists of the configuration directives that should be set on every
fresh installation.



Configuration scenarios
=======================

Simple installation
-------------------
Put your configuration file in ``data/config.php``.
If you installed SemanticScuttle's PEAR package, use::

    $ pear config-get data_dir
    /usr/share/php/data

to find the data directory location and append ``SemanticScuttle/`` to it.
In this case, the configuration file has to be in::

    /usr/share/php/data/SemanticScuttle/config.php


The configuration file may also be saved into::

    /etc/semanticscuttle/config.php


Multiple SemanticScuttle instances
----------------------------------
The files of one single SemanticScuttle installation may be shared
for several SemanticScuttle instances.

To be able to configure them differently, SemanticScuttle supports
per-host configuration files:

- ``data/config.$hostname.php``
- ``/etc/semanticscuttle/config.$hostname.php``

