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
    define('PHPUnit_MAIN_METHOD', 'SearchHistoryTest::main');
}

require_once 'prepare.php';

/**
 * Unit tests for the SemanticScuttle search history service.
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Benjamin Huynh-Kim-Bang <mensonge@users.sourceforge.net>
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @author   Eric Dane <ericdane@users.sourceforge.net>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */
class SearchHistoryTest extends TestBase
{
    protected $us;
    protected $bs;
    protected $b2ts;
    protected $tts;
    protected $shs;



    /**
     * Used to run this test class standalone
     *
     * @return void
     */
    public static function main()
    {
        require_once 'PHPUnit/TextUI/TestRunner.php';
        PHPUnit_TextUI_TestRunner::run(
            new PHPUnit_Framework_TestSuite(__CLASS__)
        );
    }



    /**
     * Set up all services
     *
     * @return void
     */
    protected function setUp()
    {
        $this->us = SemanticScuttle_Service_Factory::get('User');
        $this->bs = SemanticScuttle_Service_Factory::get('Bookmark');
        $this->bs->deleteAll();

        $this->b2ts =SemanticScuttle_Service_Factory::get('Bookmark2Tag');
        $this->b2ts->deleteAll();

        $this->tts = SemanticScuttle_Service_Factory::get('Tag2Tag');
        $this->tts->deleteAll();

        $this->tsts = SemanticScuttle_Service_Factory::get('TagStat');
        $this->tsts->deleteAll();

        $this->shs = SemanticScuttle_Service_Factory::get('SearchHistory');
        $this->shs->deleteAll();
    }

    /**
     * Tests if adding searches to the database works
     *
     * @covers SemanticScuttle_Service_SearchHistory::addSearch
     */
    public function testAddSearch()
    {
        $this->assertEquals(0, $this->shs->countSearches());

        $this->assertTrue(
            $this->shs->addSearch('testsearchterm', 'all', 0)
        );
        $this->assertEquals(1, $this->shs->countSearches());
    }

    /**
     * Tests if adding a search without terms should fail
     *
     * @covers SemanticScuttle_Service_SearchHistory::addSearch
     */
    public function testAddSearchNoTerms()
    {
        $this->assertEquals(0, $this->shs->countSearches());

        $this->assertFalse(
            $this->shs->addSearch('', 'all', 0)
        );
        $this->assertEquals(0, $this->shs->countSearches());
    }

    public function testSearchHistory()
    {
        $shs = $this->shs;

        $terms = 'bbqsdkbb;,:,:q;,qddds&é"\'\\\\\(-è_çà)';
        $terms2 = '~#{|`]';
        $range = 'all';
        $nbResults = 10908;
        $uId = 10;

        $shs->addSearch($terms, $range, $nbResults, $uId);
        $shs->addSearch($terms2, $range, $nbResults, $uId);
        $shs->addSearch('', $range, $nbResults, $uId);    // A void search must not be saved

        $searches = $shs->getAllSearches();
        $this->assertSame(2, count($searches));
        $searches = $shs->getAllSearches($range, $uId);
        $this->assertEquals(2, count($searches));
        $searches = $shs->getAllSearches($range, 20);  // fake userid
        $this->assertEquals(0, count($searches));
        $searches = $shs->getAllSearches($range, $uId, 1);
        $this->assertEquals(1, count($searches));
        $searches = $shs->getAllSearches($range, null, 1, 1);
        $this->assertEquals(1, count($searches));

        //test content of results
        $searches = $shs->getAllSearches();
        $this->assertSame($terms2, $searches[0]['shTerms']);
        $this->assertSame($range, $searches[0]['shRange']);
        $this->assertEquals($nbResults, $searches[0]['shNbResults']);
        $this->assertEquals($uId, $searches[0]['uId']);
        $this->assertSame($terms, $searches[1]['shTerms']);
        $this->assertSame($range, $searches[1]['shRange']);
        $this->assertEquals($nbResults, $searches[1]['shNbResults']);
        $this->assertEquals($uId, $searches[1]['uId']);

        //test distinct parameter
        $shs->addSearch(
            $terms, $range, $nbResults, 30
        ); // we repeat a search (same terms)
        $searches = $shs->getAllSearches();
        $this->assertSame(3, count($searches));
        $searches = $shs->getAllSearches(null, null, null, null, true);
        $this->assertSame(2, count($searches));
    }

    /**
     * Deleting the oldest search without any historical searches
     *
     * @covers SemanticScuttle_Service_SearchHistory::deleteOldestSearch
     */
    public function testDeleteOldestSearchNone()
    {
        $this->assertEquals(0, $this->shs->countSearches());
        $this->assertTrue($this->shs->deleteOldestSearch());
        $this->assertEquals(0, $this->shs->countSearches());
    }

    /**
     * Test deleting the oldest search
     *
     * @covers SemanticScuttle_Service_SearchHistory::deleteOldestSearch
     */
    public function testDeleteOldestSearchSome()
    {
        $this->assertEquals(0, $this->shs->countSearches());
        $this->shs->addSearch('testsearchterm1', 'all', 0);
        $this->shs->addSearch('testsearchterm2', 'all', 0);

        $rows = $this->shs->getAllSearches();
        $this->assertEquals(2, count($rows));

        $highestId = -1;
        foreach ($rows as $row) {
            if ($row['shId'] > $highestId) {
                $highestId = $row['shId'];
            }
        }

        $this->shs->deleteOldestSearch();

        $this->assertEquals(1, $this->shs->countSearches());

        $rows = $this->shs->getAllSearches();
        $this->assertEquals(1, count($rows));
        $this->assertEquals(
            $highestId,
            $rows[0]['shId']
        );
    }



    /**
     * Test deleting all of the search history
     */
    public function testDeleteAll()
    {
        $this->shs->addSearch('testsearchterm1', 'all', 0);
        $this->shs->addSearch('testsearchterm2', 'all', 0);
        $this->shs->deleteAll();
        $this->assertEquals(0, $this->shs->countSearches());
    }
}


if (PHPUnit_MAIN_METHOD == 'SearchHistoryTest::main') {
    SearchHistoryTest::main();
}

?>
