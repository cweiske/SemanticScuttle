<?php
if(!file_exists(dirname(__FILE__) .'/config.inc.php')) {
	die("Please, create the 'config.inc.php' file. You can copy the 'config.inc.php.example' file.");
}

// 1 // First requirements part (before debug management)
require_once(dirname(__FILE__) .'/config.inc.php');
require_once(dirname(__FILE__) .'/constants.inc.php'); // some constants are based on variables from config file


// Debug Management using constants
if(DEBUG_MODE) {
	ini_set('display_errors', '1');
	ini_set('mysql.trace_mode', '1');
	error_reporting(E_ALL);
} else {
	ini_set('display_errors', '0');
	ini_set('mysql.trace_mode', '0');
	error_reporting(0);
}

// 2 // Second requirements part which could display bugs (must come after debug management)
require_once(dirname(__FILE__) .'/services/servicefactory.php');
require_once(dirname(__FILE__) .'/functions.inc.php');


// 3 // Third requirements part which import functions from includes/ directory

// UTF-8 functions
require_once(dirname(__FILE__) .'/includes/utf8.php');

// Translation
require_once(dirname(__FILE__) .'/includes/php-gettext/gettext.inc');
$domain = 'messages';
T_setlocale(LC_MESSAGES, $locale);
T_bindtextdomain($domain, dirname(__FILE__) .'/locales');
T_bind_textdomain_codeset($domain, 'UTF-8');
T_textdomain($domain);


session_start();

?>
