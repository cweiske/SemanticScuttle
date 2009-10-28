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

require_once 'prepare.php';

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'BookmarkTest::main');
}

/**
 * Unit tests for the SemanticScuttle bookmark service.
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Benjamin Huynh-Kim-Bang <mensonge@users.sourceforge.net>
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @author   Eric Dane <ericdane@users.sourceforge.net>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */
class BookmarkTest extends TestBase
{
    protected $us;
    protected $bs;
    protected $ts;
    protected $tts;



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



    protected function setUp()
    {
        $this->us = SemanticScuttle_Service_Factory::get('User');
        $this->bs = SemanticScuttle_Service_Factory::get('Bookmark');
        $this->bs->deleteAll();
        $this->b2ts= SemanticScuttle_Service_Factory::get('Bookmark2Tag');
        $this->b2ts->deleteAll();
        $this->tts = SemanticScuttle_Service_Factory::get('Tag2Tag');
        $this->tts->deleteAll();
        $this->tsts = SemanticScuttle_Service_Factory::get('TagStat');
        $this->tsts->deleteAll();
        $this->vs = SemanticScuttle_Service_Factory::get('Vote');
        $this->vs->deleteAll();
    }

    public function testHardCharactersInBookmarks()
    {
        $bs = $this->bs;
        $title = "title&é\"'(-è_çà)=";
        $desc = "description#{[|`\^@]}³<> ¹¡÷×¿&é\"'(-è\\_çà)=";
        $tag1 = "#{|`^@]³¹¡¿<&é\"'(-è\\_çà)";
        $tag2 = "&é\"'(-è.[?./§!_çà)";

        $uid = $this->addUser();
        $bid = $bs->addBookmark(
            'http://site1.com', $title, $desc, 'note',
            0, array($tag1, $tag2),
            null, false, false, $uid
        );

        $bookmarks = $bs->getBookmarks(0, 1);

        $b0 = $bookmarks['bookmarks'][0];
        $this->assertEquals($title, $b0['bTitle']);
        $this->assertEquals($desc, $b0['bDescription']);
        $this->assertEquals(
            str_replace(array('"', '\'', '/'), "_", $tag1),
            $b0['tags'][0]
        );
        $this->assertEquals(
            str_replace(array('"', '\'', '/'), "_", $tag2),
            $b0['tags'][1]
        );
    }

    public function testUnificationOfBookmarks()
    {
        $bs = $this->bs;

        $bs->addBookmark(
            'http://site1.com', "title", "description", 'note',
            0, array('tag1'), null, false, false,
            1
        );
        $bs->addBookmark(
            "http://site1.com", "title2", "description2", 'note',
            0, array('tag2'), null, false, false,
            2
        );

        $bookmarks = $bs->getBookmarks();
        $this->assertEquals(1, $bookmarks['total']);
    }

    /*public function testSearchingBookmarksAccentsInsensible()
     {
     $bs = $this->bs;

     $bs->addBookmark("http://site1.com", "title", "éèüaàê", "status", array('tag1'), null, false, false, 1);
     $bookmarks =& $bs->getBookmarks(0, NULL, NULL, NULL, $terms = "eeaae"); //void
     $this->assertEquals(0, $bookmarks['total']);
     $bookmarks =& $bs->getBookmarks(0, NULL, NULL, NULL, $terms = "eeuaae");
     $this->assertEquals(1, $bookmarks['total']);
     }*/



    /**
     * Test if deleting a bookmark works.
     *
     * @return void
     */
    public function testDeleteBookmark()
    {
        $bookmarks = $this->bs->getBookmarks();
        $this->assertEquals(0, $bookmarks['total']);

        $bid = $this->addBookmark();
        $bookmarks = $this->bs->getBookmarks();
        $this->assertEquals(1, $bookmarks['total']);

        $bid2 = $this->addBookmark();
        $bookmarks = $this->bs->getBookmarks();
        $this->assertEquals(2, $bookmarks['total']);

        $this->assertTrue($this->bs->deleteBookmark($bid));
        $bookmarks = $this->bs->getBookmarks();
        $this->assertEquals(1, $bookmarks['total']);

        $this->assertTrue($this->bs->deleteBookmark($bid2));
        $bookmarks = $this->bs->getBookmarks();
        $this->assertEquals(0, $bookmarks['total']);
    }



    /**
     * Test if deleting a bookmark with a vote works.
     *
     * @return void
     */
    public function testDeleteBookmarkWithVote()
    {
        $uid = $this->addUser();
        $bid = $this->addBookmark();

        $bid = $this->addBookmark();
        $this->vs->vote($bid, $uid, 1);
        $this->assertTrue($this->vs->hasVoted($bid, $uid));

        $bid2 = $this->addBookmark();
        $this->vs->vote($bid2, $uid, 1);
        $this->assertTrue($this->vs->hasVoted($bid2, $uid));

        $this->assertTrue($this->bs->deleteBookmark($bid));
        $this->assertFalse($this->vs->hasVoted($bid, $uid));
        $this->assertTrue($this->vs->hasVoted($bid2, $uid));
    }



    /**
     * Verify that getBookmark() does not include user voting
     * data when no user is logged on.
     *
     * @return void
     */
    public function testGetBookmarkUserVotingNoUser()
    {
        $uid = $this->addUser();
        $bid = $this->addBookmark($uid);
        //no user
        $this->us->setCurrentUserId(null);

        $bm = $this->bs->getBookmark($bid);
        $this->assertArrayNotHasKey('hasVoted', $bm);
        $this->assertArrayNotHasKey('vote', $bm);
    }



    /**
     * Verify that getBookmark() automatically includes
     * voting data of the currently logged on user,
     * even if he did not vote yet.
     *
     * @return void
     */
    public function testGetBookmarkUserVotingWithUserNoVote()
    {
        $uid = $this->addUser();
        $bid = $this->addBookmark($uid);
        //log user in
        $this->us->setCurrentUserId($uid);

        $bm = $this->bs->getBookmark($bid);
        $this->assertArrayHasKey('hasVoted', $bm);
        $this->assertArrayHasKey('vote', $bm);
        $this->assertEquals(0, $bm['hasVoted']);
        $this->assertEquals(null, $bm['vote']);
    }



    /**
     * Verify that getBookmark() automatically includes
     * voting data of the currently logged on user
     * when he voted positive.
     *
     * @return void
     */
    public function testGetBookmarkUserVotingWithUserPositiveVote()
    {
        $uid = $this->addUser();
        $bid = $this->addBookmark($uid);
        //log user in
        $this->us->setCurrentUserId($uid);
        $this->assertTrue($this->vs->vote($bid, $uid, 1));

        $bm = $this->bs->getBookmark($bid);
        $this->assertArrayHasKey('hasVoted', $bm);
        $this->assertArrayHasKey('vote', $bm);
        $this->assertEquals(1, $bm['hasVoted']);
        $this->assertEquals(1, $bm['vote']);
    }



    /**
     * Verify that getBookmark() automatically includes
     * voting data of the currently logged on user
     * when he voted positive.
     *
     * @return void
     */
    public function testGetBookmarkUserVotingWithUserNegativeVote()
    {
        $uid = $this->addUser();
        $bid = $this->addBookmark($uid);
        //log user in
        $this->us->setCurrentUserId($uid);
        $this->assertTrue($this->vs->vote($bid, $uid, -1));

        $bm = $this->bs->getBookmark($bid);
        $this->assertArrayHasKey('hasVoted', $bm);
        $this->assertArrayHasKey('vote', $bm);
        $this->assertEquals(1, $bm['hasVoted']);
        $this->assertEquals(-1, $bm['vote']);
    }

}


if (PHPUnit_MAIN_METHOD == 'BookmarkTest::main') {
    BookmarkTest::main();
}
?>
