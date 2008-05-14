<?php

// Error codes
define('GENERAL_MESSAGE', 200);
define('GENERAL_ERROR', 202);
define('CRITICAL_MESSAGE', 203);
define('CRITICAL_ERROR', 204);

// Page name
define('PAGE_INDEX', "index");
define('PAGE_BOOKMARKS', "bookmarks");


// Miscellanous

// INSTALLATION_ID is based on directory  path and used as prefix (in session and cookie) to prevent mutual login for different installations on the same host server
//define('INSTALLATION_ID', md5(dirname(realpath('.'))));
define('INSTALLATION_ID', md5($GLOBALS['dbname'].$GLOBALS['tableprefix']));

?>
