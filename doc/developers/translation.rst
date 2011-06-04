===========================
Translating SemanticScuttle
===========================

SemanticScuttle uses gnu gettext for translation. It does not
rely on the php extension but ships with a pure php implementation,
php-gettext_.

Using gettext from within the code is really easy:
Enclose the string you want to translate in a "``T_``" function call.

For example, to translate::

  echo "Vote for";

just write ::

  echo T_("Vote for");

.. _php-gettext: https://launchpad.net/php-gettext/

Translation basics
==================

We keep one base translation file, ``data/locales/messages.po``.
This file is auto-generated via ``xgettext`` from all our php source files.
In case you added a new string to the code that needs translation,
update the base translation file by running ::

  $ php scripts/update-translation-base.php

After that has been done, the changes to the base ``messages.po`` file
need to be merged into the single language translation files,
for example ``data/locales/de_DE/LC_MESSAGES/messages.po``.

Updating them from the master file is as easy as running::

  $ php scripts/update-translation.php de_DE

When the translation is ready, the ``.po`` file needs to be compiled
in a machine-readable ``.mo`` file. Use ::

  $ php scripts/compile-translation.php de_DE

to achieve that.


