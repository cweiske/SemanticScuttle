<?php
/**
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
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'AllTests::main');
}

require_once 'prepare.php';
require_once 'PHPUnit/Framework/TestSuite.php';

/**
 * SemanticScuttle unit tests.
 *
 * To launch this tests, you need PHPUnit 3.
 * Run them with:
 * $ php tests/AllTests.php
 * or single files like:
 * $ php tests/BookmarkTest.php
 *
 * You also may use phpunit directly:
 * $ phpunit tests/AllTests.php
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Benjamin Huynh-Kim-Bang <mensonge@users.sourceforge.net>
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @author   Eric Dane <ericdane@users.sourceforge.net>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */
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
        $suite->addTestFile($tdir . '/BookmarkTest.php');
        $suite->addTestFile($tdir . '/Tag2TagTest.php');
        $suite->addTestFile($tdir . '/TagsCacheTest.php');
        $suite->addTestFile($tdir . '/CommonDescriptionTest.php');
        $suite->addTestFile($tdir . '/SearchHistoryTest.php');
        $suite->addTestFile($tdir . '/TagTest.php');
        $suite->addTestFile($tdir . '/VoteTest.php');
        $suite->addTestFile($tdir . '/UserTest.php');
        return $suite;
    }



    protected function tearDown()
    {
    }
}

if (PHPUnit_MAIN_METHOD == 'AllTests::main') {
    AllTests::main();
}

?>