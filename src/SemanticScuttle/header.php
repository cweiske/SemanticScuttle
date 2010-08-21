<?php
/**
 * Base file that is used by shell scripts and www/www-header.php.
 *
 * SemanticScuttle - your social bookmark manager.
 *
 * PHP version 5.
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Benjamin Huynh-Kim-Bang <mensonge@users.sourceforge.net>
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @author   Eric Dane <ericdane@users.sourceforge.net>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */

if ('@data_dir@' == '@' . 'data_dir@') {
    //non pear-install
    $datadir = dirname(__FILE__) . '/../../data/';
} else {
    //pear installation; files are in include path
    $datadir = '@data_dir@/SemanticScuttle/';
}

if (!file_exists($datadir . '/config.php')) {
    header('HTTP/1.0 500 Internal Server Error');
    die(
        'Please copy "config.php.dist" to "config.php" in data/ folder.'
        . "\n"
    );
}
set_include_path(
    get_include_path() . PATH_SEPARATOR
    . dirname(__FILE__) . '/../'
);

// 1 // First requirements part (before debug management)
require_once $datadir . '/config.default.php';
require_once $datadir . '/config.php';

if (defined('UNIT_TEST_MODE')) {
    //make local config vars global - needed for unit tests
    //run with phpunit
    foreach (get_defined_vars() as $var => $value) {
        $GLOBALS[$var] = $value;
    }
}

// some constants are based on variables from config file
require_once 'SemanticScuttle/constants.php';


// Debug Management using constants
if (DEBUG_MODE) {
    ini_set('display_errors', '1');
    ini_set('mysql.trace_mode', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('mysql.trace_mode', '0');
    error_reporting(0);
}

// 2 // Second requirements part which could display bugs
// (must come after debug management)
require_once 'SemanticScuttle/Service.php';
require_once 'SemanticScuttle/DbService.php';
require_once 'SemanticScuttle/Service/Factory.php';
require_once 'SemanticScuttle/functions.php';

if (count($GLOBALS['serviceoverrides']) > 0
    && !defined('UNIT_TEST_MODE')
) {
    SemanticScuttle_Service_Factory::$serviceoverrides
        = $GLOBALS['serviceoverrides'];
}

// 3 // Third requirements part which import functions from includes/ directory

// UTF-8 functions
require_once 'SemanticScuttle/utf8.php';

// Translation
require_once 'php-gettext/gettext.inc';
$domain = 'messages';
T_setlocale(LC_MESSAGES, $locale);
T_bindtextdomain($domain, realpath($datadir . 'locales/'));
T_bind_textdomain_codeset($domain, 'UTF-8');
T_textdomain($domain);

// 4 // Session
if (!defined('UNIT_TEST_MODE')) {
    session_start();
    if ($GLOBALS['enableVoting']) {
        if (isset($_SESSION['lastUrl'])) {
            $GLOBALS['lastUrl'] = $_SESSION['lastUrl'];
        }
        //this here is hacky, but currently the only way to
        // differentiate between css/js php files and normal
        // http files
        if (!isset($GLOBALS['saveInLastUrl'])
            || $GLOBALS['saveInLastUrl']
        ) {
            $_SESSION['lastUrl'] = $_SERVER['REQUEST_URI'];
        }
    }
}

// 5 // Create mandatory services and objects
$userservice = SemanticScuttle_Service_Factory::get('User');
$currentUser = $userservice->getCurrentObjectUser();

$templateservice = SemanticScuttle_Service_Factory::get('Template');
$tplVars = array();
$tplVars['currentUser'] = $currentUser;
$tplVars['userservice'] = $userservice;

// 6 // Force UTF-8 behaviour for server (cannot be moved into top.inc.php which is not included into every file)
if (!defined('UNIT_TEST_MODE')) {
    //API files define that, so we need a way to support both of them
    if (!isset($httpContentType)) {
        $httpContentType = 'text/html';
    }
    if ($httpContentType !== false) {
        header('Content-Type: ' . $httpContentType . '; charset=utf-8');
    }
}
?>
