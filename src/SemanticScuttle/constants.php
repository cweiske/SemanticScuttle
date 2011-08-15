<?php
/**
 * Define constants used in all the application.
 * Some constants are based on variables from configuration file.
 *
 * SemanticScuttle - your social bookmark manager.
 *
 * PHP version 5.
 *
 * @category    Bookmarking
 * @package     SemanticScuttle
 * @subcategory Base
 * @author      Benjamin Huynh-Kim-Bang <mensonge@users.sourceforge.net>
 * @author      Christian Weiske <cweiske@cweiske.de>
 * @author      Eric Dane <ericdane@users.sourceforge.net>
 * @license     GPL http://www.gnu.org/licenses/gpl.html
 * @link        http://sourceforge.net/projects/semanticscuttle
 */

// Debug managament
if (isset($GLOBALS['debugMode'])) {
    define('DEBUG_MODE', $GLOBALS['debugMode']);
    // Constant used exclusively into db/ directory
    define('DEBUG_EXTRA', $GLOBALS['debugMode']);
}

// Determine the base URL as ROOT
define('ROOT', SemanticScuttle_Environment::getRoot());
define('ROOT_JS', ROOT . 'js/jstree-1.0-rc2/');

// Error codes
define('GENERAL_MESSAGE', 200);
define('GENERAL_ERROR', 202);
define('CRITICAL_MESSAGE', 203);
define('CRITICAL_ERROR', 204);

// Page name
define('PAGE_INDEX', "index");
define('PAGE_BOOKMARKS', "bookmarks");
define('PAGE_WATCHLIST', "watchlist");


// Miscellanous

// INSTALLATION_ID is based on directory DB and used as prefix
// (in session and cookie) to prevent mutual login for different
// installations on the same host server
define('INSTALLATION_ID', md5($GLOBALS['dbname'].$GLOBALS['tableprefix']));

//fix PATH_INFO on certain hosts
$_SERVER['PATH_INFO'] = SemanticScuttle_Environment::getServerPathInfo();
?>
