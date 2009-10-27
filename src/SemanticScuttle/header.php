<?php
if (!file_exists(dirname(__FILE__) .'/../../data/config.php')) {
	die('Please copy "config.php.dist" to "config.php"');
}
set_include_path(
    get_include_path() . PATH_SEPARATOR
    . dirname(__FILE__) . '/../'
);

// 1 // First requirements part (before debug management)
$datadir = dirname(__FILE__) . '/../../data/';
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


// 3 // Third requirements part which import functions from includes/ directory

// UTF-8 functions
require_once 'SemanticScuttle/utf8.php';

// Translation
require_once 'php-gettext/gettext.inc';
$domain = 'messages';
T_setlocale(LC_MESSAGES, $locale);
T_bindtextdomain($domain, dirname(__FILE__) . '/locales');
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

// 6 // Force UTF-8 behaviour for server (cannot be move into top.inc.php which is not included into every file)
if (!defined('UNIT_TEST_MODE')) {
    header('Content-Type: text/html; charset=utf-8');
}
?>
