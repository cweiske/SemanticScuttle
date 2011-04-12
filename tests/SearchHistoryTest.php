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

    /**
     * Tests if adding a search deletes the history if it is too
     * large.
     *
     * @covers SemanticScuttle_Service_SearchHistory::addSearch
     */
    public function testAddSearchDeleteHistory()
    {
        $this->assertEquals(0, $this->shs->countSearches());

        $this->shs->sizeSearchHistory = 5;
        $this->shs->addSearch('eins', 'all', 1);
        $this->shs->addSearch('zwei', 'all', 1);
        $this->shs->addSearch('drei', 'all', 1);
        $this->shs->addSearch('view', 'all', 1);
        $this->shs->addSearch('fünf', 'all', 1);
        $this->assertEquals(5, $this->shs->countSearches());

        $this->shs->addSearch('sechs', 'all', 1);
        $this->assertEquals(5, $this->shs->countSearches());

        $this->shs->sizeSearchHistory = 6;
        $this->shs->addSearch('sieben', 'all', 1);
        $this->assertEquals(6, $this->shs->countSearches());
        $this->shs->addSearch('acht', 'all', 1);
        $this->assertEquals(6, $this->shs->countSearches());
    }

    /**
     * Test getAllSearches() without any parameters
     */
    public function testGetAllSearches()
    {
        $this->assertEquals(0, $this->shs->countSearches());

        $this->shs->addSearch('eins', 'all', 1);
        $this->shs->addSearch('zwei', 'all', 1);
        $this->shs->addSearch('drei', 'all', 1);

        $rows = $this->shs->getAllSearches();
        $this->assertEquals(3, count($rows));

        $terms = array();
        foreach ($rows as $row) {
            $terms[] = $row['shTerms'];
        }
        sort($terms);
        $this->assertEquals(
            array('drei', 'eins', 'zwei'),
            $terms
        );
    }

    /**
     * Test getAllSearches() return value row array keys.
     */
    public function testGetAllSearchesTypes()
    {
        $this->assertEquals(0, $this->shs->countSearches());

        $this->shs->addSearch('eins', 'all', 1);

        $rows = $this->shs->getAllSearches();
        $this->assertEquals(1, count($rows));
        $row = reset($rows);

        $this->assertArrayHasKey('shTerms', $row);
        $this->assertArrayHasKey('shId', $row);
        $this->assertArrayHasKey('shRange', $row);
        $this->assertArrayHasKey('shNbResults', $row);
        $this->assertArrayHasKey('shDatetime', $row);
        $this->assertArrayHasKey('uId', $row);
    }

    /**
     * Test getAllSearches() range parameter
     */
    public function testGetAllSearchesRange()
    {
        $this->assertEquals(0, $this->shs->countSearches());

        $this->shs->addSearch('eins', 'all', 1);
        $this->shs->addSearch('zwei', 'watchlist', 1);
        $this->shs->addSearch('drei', 'watchlist', 1);
        $this->shs->addSearch('vier', 'user1', 1);
        $this->shs->addSearch('fünf', 'user2', 1);

        $rows = $this->shs->getAllSearches('all');
        $this->assertEquals(1, count($rows));

        $rows = $this->shs->getAllSearches('watchlist');
        $this->assertEquals(2, count($rows));

        $rows = $this->shs->getAllSearches('user0');
        $this->assertEquals(0, count($rows));

        $rows = $this->shs->getAllSearches('user1');
        $this->assertEquals(1, count($rows));
        $this->assertEquals('vier', $rows[0]['shTerms']);
    }

    /**
     * Test getAllSearches() uId parameter
     */
    public function testGetAllSearchesUid()
    {
        $this->assertEquals(0, $this->shs->countSearches());

        $this->shs->addSearch('eins', 'all', 1, 0);
        $this->shs->addSearch('zwei', 'all', 1, 0);
        $this->shs->addSearch('drei', 'all', 1, 1);

        $rows = $this->shs->getAllSearches(null, null);
        $this->assertEquals(3, count($rows));

        $rows = $this->shs->getAllSearches(null, 1);
        $this->assertEquals(1, count($rows));
        $this->assertEquals('drei', $rows[0]['shTerms']);
    }

    /**
     * Test getAllSearches() number parameter
     */
    public function testGetAllSearchesNb()
    {
        $this->assertEquals(0, $this->shs->countSearches());

        $this->shs->addSearch('eins', 'all', 1, 0);
        $this->shs->addSearch('zwei', 'all', 1, 0);
        $this->shs->addSearch('drei', 'all', 1, 1);

        $rows = $this->shs->getAllSearches(null, null, 1);
        $this->assertEquals(1, count($rows));

        $rows = $this->shs->getAllSearches(null, null, 2);
        $this->assertEquals(2, count($rows));

        $rows = $this->shs->getAllSearches(null, null, 3);
        $this->assertEquals(3, count($rows));

        $rows = $this->shs->getAllSearches(null, null, 4);
        $this->assertEquals(3, count($rows));
    }

    /**
     * Test getAllSearches() paging start parameter
     */
    public function testGetAllSearchesStart()
    {
        $this->assertEquals(0, $this->shs->countSearches());

        $this->shs->addSearch('eins', 'all', 1, 0);
        $this->shs->addSearch('zwei', 'all', 1, 0);
        $this->shs->addSearch('drei', 'all', 1, 1);

        $rows = $this->shs->getAllSearches(null, null, 1, 0);
        $this->assertEquals(1, count($rows));
        $this->assertEquals('drei', $rows[0]['shTerms']);

        $rows = $this->shs->getAllSearches(null, null, 1, 1);
        $this->assertEquals(1, count($rows));
        $this->assertEquals('zwei', $rows[0]['shTerms']);

        $rows = $this->shs->getAllSearches(null, null, 3, 2);
        $this->assertEquals(1, count($rows));
        $this->assertEquals('eins', $rows[0]['shTerms']);
    }

    /**
     * Test getAllSearches() distinct parameter
     */
    public function testGetAllSearchesDistinct()
    {
        $this->assertEquals(0, $this->shs->countSearches());

        $this->shs->addSearch('eins', 'all', 1);
        $this->shs->addSearch('eins', 'all', 1);
        $this->shs->addSearch('drei', 'all', 1);

        $rows = $this->shs->getAllSearches(null, null, null, null, false);
        $this->assertEquals(3, count($rows));

        $rows = $this->shs->getAllSearches(null, null, null, null, true);
        $this->assertEquals(2, count($rows));
    }

    /**
     * Test getAllSearches() withResults parameter
     */
    public function testGetAllSearchesWithResults()
    {
        $this->assertEquals(0, $this->shs->countSearches());

        $this->shs->addSearch('eins', 'all', 0);
        $this->shs->addSearch('zwei', 'all', 0);
        $this->shs->addSearch('drei', 'all', 1);

        $rows = $this->shs->getAllSearches(null, null, null, null, false, false);
        $this->assertEquals(3, count($rows));

        $rows = $this->shs->getAllSearches(null, null, null, null, false, true);
        $this->assertEquals(1, count($rows));
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
     * Test if deleting the search history for a certain user works
     */
    public function testDeleteSearchHistoryForUser()
    {
        $this->assertEquals(0, $this->shs->countSearches());

        $this->shs->addSearch('eins', 'all', 1, 0);
        $this->shs->addSearch('zwei', 'all', 1, 22);
        $this->shs->addSearch('drei', 'all', 1, 1);
        $this->shs->addSearch('vier', 'all', 1, 22);

        $this->shs->deleteSearchHistoryForUser(22);
        $this->assertEquals(2, $this->shs->countSearches());

        $this->shs->deleteSearchHistoryForUser(20);
        $this->assertEquals(2, $this->shs->countSearches());

        $this->shs->deleteSearchHistoryForUser(1);
        $this->assertEquals(1, $this->shs->countSearches());
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
?>
