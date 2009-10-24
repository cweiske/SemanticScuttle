<?php
/**
 * Prepare the application for unit testing
 */
$_SERVER['HTTP_HOST'] = 'http://localhost/';
define('UNIT_TEST_MODE', true);

require_once dirname(__FILE__) . '/../src/SemanticScuttle/header.php'
?>