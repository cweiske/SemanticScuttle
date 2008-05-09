<?php
ini_set('display_errors', '1');
ini_set('mysql.trace_mode', '0');

error_reporting(E_ALL ^ E_NOTICE);

define('DEBUG', true);
session_start();

if(!file_exists(dirname(__FILE__) .'/config.inc.php')) {
    die("Please, create the 'config.inc.php' file. You can copy the 'config.inc.php.example' file.");
}

require_once(dirname(__FILE__) .'/services/servicefactory.php');
require_once(dirname(__FILE__) .'/config.inc.php');
require_once(dirname(__FILE__) .'/constants.inc.php');
require_once(dirname(__FILE__) .'/functions.inc.php');

// Determine the base URL
if (!isset($root)) {
    $pieces = explode('/', $_SERVER['SCRIPT_NAME']);
    $root = '/';
    foreach($pieces as $piece) {
        if ($piece != '' && !strstr($piece, '.php')) {
            $root .= $piece .'/';
        }
    }
    if (($root != '/') && (substr($root, -1, 1) != '/')) {
        $root .= '/';
    }
    $root = 'http://'. $_SERVER['HTTP_HOST'] . $root;
}
?>
