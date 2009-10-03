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

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'AllTests::main');
}

require_once 'PHPUnit/Framework/TestSuite.php';

class AllTests extends PHPUnit_Framework_TestSuite
{
    public static function main()
    {
        require_once 'PHPUnit/TextUI/TestRunner.php';
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }


    public static function suite()
    {
    	$suite = new AllTests();
        $tdir = dirname(__FILE__);
    	$suite->addTestFile($tdir . '/BookmarksTest.php');
    	$suite->addTestFile($tdir . '/Tag2TagTest.php'); 
    	$suite->addTestFile($tdir . '/TagsCacheTest.php');
    	$suite->addTestFile($tdir . '/CommonDescriptionTest.php');     	
    	$suite->addTestFile($tdir . '/SearchTest.php'); 
    	$suite->addTestFile($tdir . '/TagsTest.php');
        return $suite;
    }
 
    protected function setUp()
    {
    	global $dbhost, $dbuser, $dbpass, $dbname, $dbport, $dbpersist, $dbtype,
            $tableprefix, $TEMPLATES_DIR, $filetypes, $debugMode;
		require_once dirname(__FILE__) . '/../src/SemanticScuttle/header.php';
    }
 
    protected function tearDown()
    {
    }
}

if (PHPUnit_MAIN_METHOD == 'AllTests::main') {
    AllTests::main();
}

?>