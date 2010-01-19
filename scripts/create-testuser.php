<?php
/**
 * Simply create a test user in the database with "test" as password
 */
require_once dirname(__FILE__) . '/../src/SemanticScuttle/header-standalone.php';

$us = SemanticScuttle_Service_Factory::get('User');
$us->addUser('test', 'test', 'test@example.org');
$us->addUser('admin', 'admin', 'admin@example.org');
?>