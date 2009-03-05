<?php
/*
 * Define constants used in all the application.
 * Some constants are based on variables from configuration file.
 */

// Debug managament
if(isset($GLOBALS['debugMode'])) {
	define('DEBUG_MODE', $GLOBALS['debugMode']);
	define('DEBUG_EXTRA', $GLOBALS['debugMode']); // Constant used exclusively into db/ directory
}

// Determine the base URL as ROOT
if (!isset($GLOBALS['root'])) {
	$pieces = explode('/', $_SERVER['SCRIPT_NAME']);
	
	$rootTmp = '/';
	foreach($pieces as $piece) {
		//we eliminate possible sscuttle subfolders (like gsearch for example)
		if ($piece != '' && !strstr($piece, '.php') && $piece != 'gsearch') {
			$rootTmp .= $piece .'/';
		}
	}
	if (($rootTmp != '/') && (substr($rootTmp, -1, 1) != '/')) {
		$rootTmp .= '/';
	}

	define('ROOT', 'http://'. $_SERVER['HTTP_HOST'] . $rootTmp);
} else {
	define('ROOT', $GLOBALS['root']);
}

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

// INSTALLATION_ID is based on directory DB and used as prefix (in session and cookie) to prevent mutual login for different installations on the same host server
define('INSTALLATION_ID', md5($GLOBALS['dbname'].$GLOBALS['tableprefix']));

// Correct bugs with PATH_INFO (maybe for Apache 1 or CGI) -- for 1&1 host...
if(strlen($_SERVER["PATH_INFO"])<strlen($_SERVER["ORIG_PATH_INFO"])) {
	$_SERVER["PATH_INFO"] = $_SERVER["ORIG_PATH_INFO"];
}
if(strcasecmp($_SERVER["PATH_INFO"], $_SERVER["SCRIPT_NAME "]) == 0) {
	unset($_SERVER["PATH_INFO"]);
}
if(strpos($_SERVER["PATH_INFO"], '.php') !== false) {
	unset($_SERVER["PATH_INFO"]);
}
?>
