<?php

/*
* To launch all tests, type the following line into the root directory
* of SemanticScuttle (where is the config.inc.php file) :
* 
*     phpunit --testdox-html tests/dox.html AllTests tests/allTests.php
* 
*  !!Check that $debugMode = false in config.inc.php to avoid unstable beahviours!!
* 
* A dox.html file will be created into the tests/ directory providing a summary
* of tests according to agile development.
* */

class AllTests extends PHPUnit_Framework_TestSuite
{
    public static function suite()
    {
    	$suite = new AllTests();
    	$suite->addTestFile('tests/bookmarksTest.php');
    	$suite->addTestFile('tests/tag2TagTest.php'); 
    	$suite->addTestFile('tests/tagsCacheTest.php');
    	$suite->addTestFile('tests/commonDescriptionTest.php');     	
    	$suite->addTestFile('tests/searchTest.php'); 
    	$suite->addTestFile('tests/tagsTest.php');
        return $suite;
    }
 
    protected function setUp()
    {
    	global $dbhost, $dbuser, $dbpass, $dbname, $dbport, $dbpersist, $dbtype, $tableprefix, $TEMPLATES_DIR, $filetypes, $debugMode;
		require_once('./header.inc.php');
    }
 
    protected function tearDown()
    {
    }
}
?>