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

/**
 * SemanticScuttle unit tests.
 *
 * To launch this tests, you need PHPUnit 3.
 * Run them with:
 * $ cd tests; phpunit .
 * or single files like:
 * $ cd tests; phpunit BookmarkTest.php
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
    public static function suite()
    {
        $suite = new AllTests();
        $tdir = dirname(__FILE__);
        $suite->addTestFile($tdir . '/BookmarkTest.php');
        $suite->addTestFile($tdir . '/Bookmark2TagTest.php');
        $suite->addTestFile($tdir . '/Tag2TagTest.php');
        $suite->addTestFile($tdir . '/TagsCacheTest.php');
        $suite->addTestFile($tdir . '/CommonDescriptionTest.php');
        $suite->addTestFile($tdir . '/SearchHistoryTest.php');
        $suite->addTestFile($tdir . '/TagTest.php');
        $suite->addTestFile($tdir . '/VoteTest.php');
        $suite->addTestFile($tdir . '/UserTest.php');
        $suite->addTestFile($tdir . '/Api/ExportCsvTest.php');
        $suite->addTestFile($tdir . '/Api/OpenSearchTest.php');
        $suite->addTestFile($tdir . '/Api/PostsAddTest.php');
        $suite->addTestFile($tdir . '/Api/PostsDeleteTest.php');
        $suite->addTestFile($tdir . '/Api/PostsUpdateTest.php');
        return $suite;
    }



    protected function tearDown()
    {
    }
}
?>
