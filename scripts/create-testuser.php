<?php
/**
 * Simply create a test user in the database with "test" as password
 */
$_SERVER['HTTP_HOST'] = 'http://localhost/';
define('UNIT_TEST_MODE', true);

require_once dirname(__FILE__) . '/../src/SemanticScuttle/header.php';

$us = SemanticScuttle_Service_Factory::get('User');
$us->addUser('test', 'test', 'test@example.org');
?>