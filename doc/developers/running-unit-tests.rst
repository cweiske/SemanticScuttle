Running unit tests
==================

Go to the SemanticScuttle ``tests`` directory and run ``phpunit``::

  $ cd tests
  $ phpunit .

also remember the ``--verbose`` parameter to PHPUnit.

If you want to run a specific test class only: ::

 $ cd tests
 $ phpunit BookmarksTest.php

If you need to test one method only: ::

 $ cd tests
 $ phpunit --filter BookmarkTest::testUnificationOfBookmarks tests/BookmarkTest.php


Caveats
-------
Having debugging enabled and database driver "``mysql4``" activated
will lead to failing tests because of ``FOUND_ROWS()`` usage, which
does not work nicely with database debugging.
