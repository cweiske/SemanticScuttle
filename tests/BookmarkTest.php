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

        $uid  = $this->addUser();
        $uid2 = $this->addUser();

        $bs->addBookmark(
            'http://site1.com', "title", "description", 'note',
            0, array('tag1'), null, false, false,
            $uid
        );
        $bs->addBookmark(
            "http://site1.com", "title2", "description2", 'note',
            0, array('tag2'), null, false, false,
            $uid2
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
     * Tests if bookmarkExists() returns false when the given
     * parameter is invalid.
     *
     * @return void
     */
    public function testBookmarkExistsInvalidParam()
    {
        $this->assertFalse($this->bs->bookmarkExists(false));
        $this->assertFalse($this->bs->bookmarkExists(null));
    }



    /**
     * Tests if bookmarkExists() returns true when a bookmark
     * exists
     *
     * @return void
     */
    public function testBookmarkExistsTrue()
    {
        $bid = $this->addBookmark();
        $bookmark = $this->bs->getBookmark($bid);

        $this->assertTrue($this->bs->bookmarkExists($bookmark['bAddress']));
    }



    /**
     * Tests if bookmarkExists() returns false when a bookmark
     * does not exist
     *
     * @return void
     */
    public function testBookmarkExistsFalse()
    {
        $this->assertFalse($this->bs->bookmarkExists('does-not-exist'));
    }



    /**
     * Tests if bookmarkExists() returns true when a bookmark
     * exists for a user
     *
     * @return void
     */
    public function testBookmarkExistsUserTrue()
    {
        $bid = $this->addBookmark();
        $bookmark = $this->bs->getBookmark($bid);

        $this->assertTrue(
            $this->bs->bookmarkExists(
                $bookmark['bAddress'],
                $bookmark['uId']
            )
        );
    }



    /**
     * Tests if bookmarkExists() returns false when a bookmark
     * does not exist for a user
     *
     * @return void
     */
    public function testBookmarkExistsUserFalse()
    {
        $this->assertFalse(
            $this->bs->bookmarkExists('does-not-exist', 1234)
        );
    }



    /**
     * Tests if bookmarkExists() returns false when a bookmark
     * does not exist for a user but for another user
     *
     * @return void
     */
    public function testBookmarkExistsOtherUser()
    {
        $bid = $this->addBookmark();
        $bookmark = $this->bs->getBookmark($bid);

        $this->assertFalse(
            $this->bs->bookmarkExists(
                $bookmark['bAddress'],
                $bookmark['uId'] + 1
            )
        );
    }



    /**
     * Test if countBookmarks() works with no bookmarks
     *
     * @return void
     */
    public function testCountBookmarksNone()
    {
        $uid = $this->addUser();
        $this->assertEquals(0, $this->bs->countBookmarks($uid));
        $this->assertEquals(0, $this->bs->countBookmarks($uid, 'public'));
        $this->assertEquals(0, $this->bs->countBookmarks($uid, 'private'));
        $this->assertEquals(0, $this->bs->countBookmarks($uid, 'shared'));
        $this->assertEquals(0, $this->bs->countBookmarks($uid, 'all'));
    }



    /**
     * Test if countBookmarks() works with one public bookmark
     *
     * @return void
     */
    public function testCountBookmarksOnePublic()
    {
        $uid = $this->addUser();
        $this->addBookmark($uid);
        $this->assertEquals(1, $this->bs->countBookmarks($uid));
        $this->assertEquals(1, $this->bs->countBookmarks($uid, 'public'));
        $this->assertEquals(0, $this->bs->countBookmarks($uid, 'private'));
        $this->assertEquals(0, $this->bs->countBookmarks($uid, 'shared'));
        $this->assertEquals(1, $this->bs->countBookmarks($uid, 'all'));
    }



    /**
     * Test if countBookmarks() works with one private bookmark
     *
     * @return void
     */
    public function testCountBookmarksOnePrivate()
    {
        $uid = $this->addUser();
        $this->bs->addBookmark(
            'http://test', 'test', 'desc', 'note',
            2,//private
            array(), null, false, false, $uid
        );
        $this->assertEquals(0, $this->bs->countBookmarks($uid));
        $this->assertEquals(0, $this->bs->countBookmarks($uid, 'public'));
        $this->assertEquals(1, $this->bs->countBookmarks($uid, 'private'));
        $this->assertEquals(0, $this->bs->countBookmarks($uid, 'shared'));
        $this->assertEquals(1, $this->bs->countBookmarks($uid, 'all'));
    }



    /**
     * Test if countBookmarks() works with one shared bookmark
     *
     * @return void
     */
    public function testCountBookmarksOneShared()
    {
        $uid = $this->addUser();
        $this->bs->addBookmark(
            'http://test', 'test', 'desc', 'note',
            1,//shared
            array(), null, false, false, $uid
        );
        $this->assertEquals(0, $this->bs->countBookmarks($uid));
        $this->assertEquals(0, $this->bs->countBookmarks($uid, 'public'));
        $this->assertEquals(0, $this->bs->countBookmarks($uid, 'private'));
        $this->assertEquals(1, $this->bs->countBookmarks($uid, 'shared'));
        $this->assertEquals(1, $this->bs->countBookmarks($uid, 'all'));
    }



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
     * Test if deleting all bookmarks for a user works.
     *
     * @return void
     */
    public function testDeleteBookmarksForUser()
    {
        $uid = $this->addUser();
        $bookmarks = $this->bs->getBookmarks(0, null, $uid);
        $this->assertEquals(0, $bookmarks['total']);

        $this->addBookmark($uid);
        $this->addBookmark($uid);
        $bookmarks = $this->bs->getBookmarks(0, null, $uid);
        $this->assertEquals(2, $bookmarks['total']);

        $this->bs->deleteBookmarksForUser($uid);
        $bookmarks = $this->bs->getBookmarks(0, null, $uid);
        $this->assertEquals(0, $bookmarks['total']);
    }



    /**
     * Test if deleting all bookmarks for a user works
     * and does not damage other user's bookmarks.
     *
     * @return void
     */
    public function testDeleteBookmarksForUserOthers()
    {
        $uidOther = $this->addUser();
        $this->addBookmark($uidOther);

        $uid = $this->addUser();
        $bookmarks = $this->bs->getBookmarks(0, null, $uid);
        $this->assertEquals(0, $bookmarks['total']);

        $this->addBookmark($uid);
        $this->addBookmark($uid);
        $bookmarks = $this->bs->getBookmarks(0, null, $uid);
        $this->assertEquals(2, $bookmarks['total']);

        $this->bs->deleteBookmarksForUser($uid);
        $bookmarks = $this->bs->getBookmarks(0, null, $uid);
        $this->assertEquals(0, $bookmarks['total']);

        $bookmarks = $this->bs->getBookmarks(0, null, $uidOther);
        $this->assertEquals(1, $bookmarks['total']);
    }



    /**
     * Test if deleting a bookmark with a vote works.
     *
     * @return void
     */
    public function testDeleteBookmarkWithVote()
    {
        $GLOBALS['enableVoting'] = true;

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
     * Test if editAllowed() returns false when the bookmark
     * id is invalid.
     *
     * @return void
     */
    public function testEditAllowedInvalidBookmarkId()
    {
        $this->assertFalse($this->bs->editAllowed('invalid'));
        $this->assertFalse($this->bs->editAllowed(array()));
        $this->assertFalse($this->bs->editAllowed(array('some', 'where')));
        $this->assertFalse($this->bs->editAllowed(array('bId' => false)));
        $this->assertFalse($this->bs->editAllowed(array('bId' => 'foo')));
    }



    /**
     * Test if editAllowed() works when passing the ID of
     * an existing bookmark.
     *
     * @return void
     */
    public function testEditAllowedBookmarkId()
    {
        $uid = $this->addUser();
        $bid = $this->addBookmark($uid);
        $this->us->setCurrentUserId($uid);
        $this->assertTrue($this->bs->editAllowed($bid));
    }



    /**
     * Test if editAllowed() works when passing the ID of
     * an existing bookmark that does not belong to the current
     * user.
     *
     * @return void
     */
    public function testEditAllowedBookmarkIdNotOwn()
    {
        $uid = $this->addUser();
        $bid = $this->addBookmark();
        $this->us->setCurrentUserId($uid);
        $this->assertFalse($this->bs->editAllowed($bid));
    }



    /**
     * Test if editAllowed() works when passing the ID of
     * an existing bookmark that does not belong to the current
     * user.
     *
     * @return void
     */
    public function testEditAllowedBookmarkIdNoUser()
    {
        $bid = $this->addBookmark();
        $this->us->setCurrentUserId(null);
        $this->assertFalse($this->bs->editAllowed($bid));
    }



    /**
     * Test if editAllowed() works when passing a bookmark
     * row.
     *
     * @return void
     */
    public function testEditAllowedBookmarkRow()
    {
        $uid = $this->addUser();
        $this->us->setCurrentUserId($uid);

        $bid = $this->addBookmark($uid);
        $bookmark = $this->bs->getBookmark($bid);
        $this->assertTrue($this->bs->editAllowed($bookmark));
    }



    /**
     * Test if editAllowed() returns false when the bookmark
     * specified by the ID does not exist.
     *
     * @return void
     */
    public function testEditAllowedIdNotFound()
    {
        $this->assertFalse($this->bs->editAllowed(98765));
    }



    /**
     * Test if editAllowed() works when the user is an administrator.
     *
     * @return void
     */
    public function testEditAllowedBookmarkAdmin()
    {
        //make the user admin
        $uid = $this->addUser();
        $user = $this->us->getUser($uid);
        $GLOBALS['admin_users'][] = $user['username'];

        $bid = $this->addBookmark($uid);
        $this->us->setCurrentUserId($uid);
        $this->assertTrue($this->bs->editAllowed($bid));
    }



    /**
     * Verify that getBookmark() returns false when the
     * bookmark cannot be found.
     *
     * @return void
     */
    public function testGetBookmarkNotFound()
    {
        $this->assertFalse($this->bs->getBookmark(987654));
    }



    /**
     * Verify that getBookmark() returns false when the
     * bookmark ID is not numeric
     *
     * @return void
     */
    public function testGetBookmarkInvalidParam()
    {
        $this->assertFalse($this->bs->getBookmark('foo'));
    }



    /**
     * Check tag loading functionality of getBookmark()
     *
     * @return void
     */
    public function testGetBookmarkIncludeTags()
    {
        $uid = $this->addUser();
        $bid = $this->addBookmark($uid);
        $this->b2ts->attachTags($bid, array('foo', 'bar'));
        $bid2 = $this->addBookmark($uid);
        $this->b2ts->attachTags($bid2, array('fuu', 'baz'));

        $bm = $this->bs->getBookmark($bid, true);
        $this->assertArrayHasKey('tags', $bm);
        $this->assertType('array', $bm['tags']);
        $this->assertContains('foo', $bm['tags']);
        $this->assertContains('bar', $bm['tags']);
    }



    /**
     * Verify that getBookmark() does not include user voting
     * data when no user is logged on.
     *
     * @return void
     */
    public function testGetBookmarkUserVotingNoUser()
    {
        $GLOBALS['enableVoting'] = true;

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
        $GLOBALS['enableVoting'] = true;

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
        $GLOBALS['enableVoting'] = true;

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
        $GLOBALS['enableVoting'] = true;

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



    public function testNormalize()
    {
        $this->assertEquals(
            'http://example.org', $this->bs->normalize('http://example.org')
        );
        $this->assertEquals(
            'ftp://example.org', $this->bs->normalize('ftp://example.org')
        );
        $this->assertEquals(
            'http://example.org', $this->bs->normalize('http://example.org/')
        );
        $this->assertEquals(
            'http://example.org', $this->bs->normalize('example.org')
        );
        $this->assertEquals(
            'mailto:foo@example.org',
            $this->bs->normalize('mailto:foo@example.org')
        );
    }



    /**
     * test if updating an existing bookmark works
     */
    public function testUpdateBookmark()
    {
        $bid = $this->addBookmark();
        $this->assertTrue(
            $this->bs->updateBookmark(
                $bid,
                'http://example.org/foo',
                'my new title',
                'new description',
                'new private note',
                1,
                array('new')
            )
        );
        $bm = $this->bs->getBookmark($bid, true);
        $this->assertEquals('http://example.org/foo', $bm['bAddress']);
        $this->assertEquals('my new title', $bm['bTitle']);
        $this->assertEquals('new description', $bm['bDescription']);
        $this->assertEquals('new private note', $bm['bPrivateNote']);
        $this->assertEquals(1, $bm['bStatus']);
        $this->assertType('array', $bm['tags']);
        $this->assertEquals(1, count($bm['tags']));
        $this->assertContains('new', $bm['tags']);
    }
}


if (PHPUnit_MAIN_METHOD == 'BookmarkTest::main') {
    BookmarkTest::main();
}
?>
