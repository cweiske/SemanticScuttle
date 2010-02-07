Running unit tests
==================

Go to the SemanticScuttle main directory and run
 $ php tests/AllTests.php
or
 $ phpunit tests/AllTests.php
also remember the --verbose parameter to PHPUnit.

If you want to run a specific test class only:
 $ phpunit tests/BookmarksTest.php

If you need to test one method only:
 $ phpunit --filter BookmarkTest::testUnificationOfBookmarks tests/BookmarkTest.php


Caveats
-------
Having debugging enabled and database driver "mysql4" activated
will lead to failing tests because of FOUND_ROWS() usage, which
does not work nicely with database debugging.
