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

    /**
     * Tests if adding a bookmark with short url name
     * saves it in the database.
     */
    public function testAddBookmarkShort()
    {
        $bid = $this->bs->addBookmark(
            'http://example.org', 'title', 'desc', 'priv',
            0, array(), 'myShortName'
        );
        $bm = $this->bs->getBookmark($bid);
        $this->assertEquals('http://example.org', $bm['bAddress']);
        $this->assertArrayHasKey('bShort', $bm);
        $this->assertEquals('myShortName', $bm['bShort']);
    }

    public function testAddBookmarkInvalidUrl()
    {
        $retval = $this->bs->addBookmark(
            'javascript:alert(123)', 'title', 'desc', 'priv',
            0, array()
        );
        $this->assertFalse($retval, 'Bookmark with invalid URL was accepted');
    }

    public function testAddBookmarkWithSpecialCharacters()
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
            null, null, false, false, $uid
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

    public function testAddBookmarkUnification()
    {
        $bs = $this->bs;

        $uid  = $this->addUser();
        $uid2 = $this->addUser();

        $bs->addBookmark(
            'http://site1.com', "title", "description", 'note',
            0, array('tag1'), null, null, false, false,
            $uid
        );
        $bs->addBookmark(
            "http://site1.com", "title2", "description2", 'note',
            0, array('tag2'), null, null, false, false,
            $uid2
        );

        $bookmarks = $bs->getBookmarks();
        $this->assertEquals(1, $bookmarks['total']);
    }

    /*public function testSearchingBookmarksAccentsInsensible()
     {
     $bs = $this->bs;

     $bs->addBookmark("http://site1.com", "title", "éèüaàê", "status", array('tag1'), null, false, false, 1);
     $bookmarks = $bs->getBookmarks(0, NULL, NULL, NULL, $terms = "eeaae"); //void
     $this->assertEquals(0, $bookmarks['total']);
     $bookmarks = $bs->getBookmarks(0, NULL, NULL, NULL, $terms = "eeuaae");
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
     * Tests if bookmarksExist() returns true when a bookmark
     * exists
     *
     * @return void
     */
    public function testBookmarksExistTrueSingle()
    {
        $bid = $this->addBookmark();
        $bookmark = $this->bs->getBookmark($bid);

        $ret = $this->bs->bookmarksExist(array($bookmark['bAddress']));
        $this->assertInternalType('array', $ret);
        $this->assertEquals(1, count($ret));
        $this->assertTrue($ret[$bookmark['bAddress']]);
    }



    /**
     * Tests if bookmarksExist() returns true when all bookmarks
     * exist
     *
     * @return void
     */
    public function testBookmarksExistTrueMultiple()
    {
        $bid = $this->addBookmark();
        $bookmark = $this->bs->getBookmark($bid);

        $bid2 = $this->addBookmark();
        $bookmark2 = $this->bs->getBookmark($bid2);


        $ret = $this->bs->bookmarksExist(
            array(
                $bookmark['bAddress'],
                $bookmark2['bAddress']
            )
        );
        $this->assertInternalType('array', $ret);
        $this->assertEquals(2, count($ret));
        $this->assertTrue($ret[$bookmark['bAddress']]);
        $this->assertTrue($ret[$bookmark2['bAddress']]);
    }



    /**
     * Tests if bookmarksExist() returns false when a bookmark
     * does not exist
     *
     * @return void
     */
    public function testBookmarksExistFalseSingle()
    {
        $ret = $this->bs->bookmarksExist(array('does-not-exist'));
        $this->assertInternalType('array', $ret);
        $this->assertEquals(1, count($ret));
        $this->assertFalse($ret['does-not-exist']);
    }



    /**
     * Tests if bookmarksExist() returns false when all bookmarks
     * do not exist
     *
     * @return void
     */
    public function testBookmarksExistFalseMultiple()
    {
        $bms = array(
            'does-not-exist',
            'does-not-exist-2',
            'does-not-exist-3',
        );
        $ret = $this->bs->bookmarksExist($bms);
        $this->assertInternalType('array', $ret);
        $this->assertEquals(3, count($ret));
        $this->assertFalse($ret['does-not-exist']);
        $this->assertFalse($ret['does-not-exist-2']);
        $this->assertFalse($ret['does-not-exist-3']);
    }



    /**
     * Tests if bookmarksExist() returns true when some bookmarks
     * exist.
     *
     * @return void
     */
    public function testBookmarksExistSome()
    {
        $bid = $this->addBookmark();
        $bookmark = $this->bs->getBookmark($bid);

        $bid2 = $this->addBookmark();
        $bookmark2 = $this->bs->getBookmark($bid2);

        //do not search for this one
        $bid3 = $this->addBookmark();
        $bookmark3 = $this->bs->getBookmark($bid3);


        $ret = $this->bs->bookmarksExist(
            array(
                $bookmark['bAddress'],
                'does-not-exist',
                $bookmark2['bAddress'],
                'does-not-exist-2',
                'does-not-exist-3'
            )
        );
        $this->assertInternalType('array', $ret);
        $this->assertEquals(5, count($ret));
        $this->assertTrue($ret[$bookmark['bAddress']]);
        $this->assertTrue($ret[$bookmark2['bAddress']]);
        $this->assertFalse($ret['does-not-exist']);
        $this->assertFalse($ret['does-not-exist-2']);
        $this->assertFalse($ret['does-not-exist-3']);
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
            array(), null, null, false, false, $uid
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
            array(), null, null, false, false, $uid
        );
        $this->assertEquals(0, $this->bs->countBookmarks($uid));
        $this->assertEquals(0, $this->bs->countBookmarks($uid, 'public'));
        $this->assertEquals(0, $this->bs->countBookmarks($uid, 'private'));
        $this->assertEquals(1, $this->bs->countBookmarks($uid, 'shared'));
        $this->assertEquals(1, $this->bs->countBookmarks($uid, 'all'));
    }



    /**
     * Check tag loading functionality of getBookmarks()
     *
     * @return void
     */
    public function testGetBookmarksIncludeTags()
    {
        $uid = $this->addUser();
        $bid = $this->addBookmark($uid);
        $this->b2ts->attachTags($bid, array('foo', 'bar'));
        $bid2 = $this->addBookmark($uid);
        $this->b2ts->attachTags($bid2, array('fuu', 'baz'));

        $bms = $this->bs->getBookmarks();
        $this->assertEquals(2, count($bms['bookmarks']));
        $this->assertEquals(2, $bms['total']);

        foreach ($bms['bookmarks'] as $bm) {
            $this->assertArrayHasKey('tags', $bm);
            $this->assertInternalType('array', $bm['tags']);
            if ($bm['bId'] == $bid) {
                $this->assertContains('foo', $bm['tags']);
                $this->assertContains('bar', $bm['tags']);
            } else if ($bm['bId'] == $bid2) {
                $this->assertContains('fuu', $bm['tags']);
                $this->assertContains('baz', $bm['tags']);
            } else {
                $this->assertTrue(false, 'Unknown bookmark id');
            }
        }
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
        $this->assertInternalType('array', $bm['tags']);
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



    /**
     * Tests if getBookmarkByAddress() works correctly.
     *
     * @return void
     */
    public function testGetBookmarkByAddress()
    {
        $url = 'http://example.org';
        $uid = $this->addUser();
        $bid = $this->addBookmark($uid, $url);

        $bm = $this->bs->getBookmarkByAddress($url);
        $this->assertInternalType('array', $bm);
        $this->assertEquals($url, $bm['bAddress']);
    }



    /**
     * Tests if getBookmarkByAddress() works correctly with aliases.
     * When passing an incomplete address i.e. without protocol,
     * the full URL needs to be searched for.
     *
     * The failure of this test lead to #2953732.
     *
     * @return void
     *
     * @link https://sourceforge.net/tracker/?func=detail&atid=1017430&aid=2953732&group_id=211356
     */
    public function testGetBookmarkByAddressAlias()
    {
        $url = 'http://example.org';
        $incomplete = 'example.org';

        $uid = $this->addUser();
        $bid = $this->addBookmark($uid, $url);

        $bm = $this->bs->getBookmarkByAddress($incomplete);
        $this->assertInternalType('array', $bm);
        $this->assertEquals($url, $bm['bAddress']);
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
        $this->assertInternalType('array', $bm['tags']);
        $this->assertEquals(1, count($bm['tags']));
        $this->assertContains('new', $bm['tags']);
    }

    /**
     * Tests if updating a bookmark's short url name
     * saves it in the database.
     *
     * @return void
     */
    public function testUpdateBookmarkShort()
    {
        $bid = $this->bs->addBookmark(
            'http://example.org', 'title', 'desc', 'priv',
            0, array(), 'myShortName'
        );
        $bm = $this->bs->getBookmark($bid);
        $this->assertEquals('myShortName', $bm['bShort']);

        $this->assertTrue(
            $this->bs->updateBookmark(
                $bid, 'http://example2.org', 'my title', 'desc',
                'priv', 0, array(), 'newShortNambb'
            )
        );
        $bm = $this->bs->getBookmark($bid);
        $this->assertEquals('newShortNambb', $bm['bShort']);
    }

    /**
     * Tests if updating a bookmark's date works.
     * This once was a bug, see bug #3073215.
     *
     * @return void
     *
     * @link https://sourceforge.net/tracker/?func=detail&atid=1017430&aid=3073215&group_id=211356
     */
    public function testUpdateBookmarkDate()
    {
        $bid = $this->bs->addBookmark(
            'http://example.org', 'title', 'desc', 'priv',
            0, array(), 'myShortName'
        );
        $bm = $this->bs->getBookmark($bid);
        $this->assertEquals('myShortName', $bm['bShort']);

        $this->assertTrue(
            $this->bs->updateBookmark(
                $bid, 'http://example2.org', 'my title', 'desc',
                'priv', 0, array(), 'newShortNambb',
                //we need to use zulu (GMT) time zone here
                // since the dates/times are stored as that
                // in the database
                '2002-03-04T05:06:07Z'
            )
        );
        $bm = $this->bs->getBookmark($bid);
        $this->assertEquals('newShortNambb', $bm['bShort']);
        $this->assertEquals('2002-03-04 05:06:07', $bm['bDatetime']);
    }



    /**
     * Test what countOther() returns when the address does not exist
     *
     * @return void
     */
    public function testCountOthersAddressDoesNotExist()
    {
        $this->assertEquals(0, $this->bs->countOthers('http://example.org'));
    }



    /**
     * Test what countOther() returns when nobody else has the same bookmark
     *
     * @return void
     */
    public function testCountOthersNone()
    {
        $uid = $this->addUser();
        $address = 'http://example.org';
        $this->addBookmark($uid, $address);
        $this->assertEquals(0, $this->bs->countOthers($address));
    }



    /**
     * Test what countOther() returns when the address exists only once
     * and multiple bookmarks are in the database.
     *
     * @return void
     */
    public function testCountOthersMultipleNone()
    {
        $uid = $this->addUser();
        $address = 'http://example.org';
        $this->addBookmark($uid, $address);
        $this->addBookmark($uid);
        $this->addBookmark($uid);
        $this->assertEquals(0, $this->bs->countOthers($address));
    }



    /**
     * Test what countOther() returns when the address exists only once
     * and the same user and other users have other bookmarks
     *
     * @return void
     */
    public function testCountOthersMultipleUsersNone()
    {
        $uid  = $this->addUser();
        $uid2 = $this->addUser();
        $address = 'http://example.org';
        $this->addBookmark($uid, $address);
        $this->addBookmark($uid);
        $this->addBookmark($uid2);
        $this->assertEquals(0, $this->bs->countOthers($address));
    }



    /**
     * Test what countOther() returns when the address exists two
     * times in the database.
     *
     * @return void
     */
    public function testCountOthersOne()
    {
        $uid  = $this->addUser();
        $uid2 = $this->addUser();
        $address = 'http://example.org';
        $this->addBookmark($uid, $address);
        $this->addBookmark($uid2, $address);
        $this->assertEquals(1, $this->bs->countOthers($address));
    }



    /**
     * Test what countOther() returns when the address exists four
     * times in the database.
     *
     * @return void
     */
    public function testCountOthersThree()
    {
        $uid  = $this->addUser();
        $address = 'http://example.org';
        $this->addBookmark($uid, $address);
        $this->addBookmark(null, $address);
        $this->addBookmark(null, $address);
        $this->addBookmark(null, $address);
        $this->assertEquals(3, $this->bs->countOthers($address));
    }



    /**
     * Test what countOther() returns when the user is logged in
     * and a friend (people on the watchlist) has bookmarked
     * and the same address with public status.
     *
     * @return void
     */
    public function testCountOthersWatchlistPublic()
    {
        $uid  = $this->addUser();
        $address = 'http://example.org';

        //create other user and add main user to his watchlist
        $friendPublic1 = $this->addUser();
        $this->us->setCurrentUserId($friendPublic1);
        $this->us->setWatchStatus($uid);

        //create bookmarks for main user and other one
        $this->addBookmark($uid, $address, 0);
        $this->addBookmark($friendPublic1,  $address, 0);//0 is public

        //log main user in
        $this->us->setCurrentUserId($uid);

        $this->assertEquals(1, $this->bs->countOthers($address));
    }



    /**
     * Test what countOther() returns when the user is logged in
     * and a friend (people on the watchlist) has bookmarked
     * and shared the same address for the watchlist.
     *
     * @return void
     */
    public function testCountOthersWatchlistShared()
    {
        $uid  = $this->addUser();
        $address = 'http://example.org';

        //create other user and add main user to his watchlist
        $friendPublic1 = $this->addUser();
        $this->us->setCurrentUserId($friendPublic1);
        $this->us->setWatchStatus($uid);

        //create bookmarks for main user and other one
        $this->addBookmark($uid, $address, 0);
        $this->addBookmark($friendPublic1,  $address, 1);//1 is shared

        //log main user in
        $this->us->setCurrentUserId($uid);

        $this->assertEquals(1, $this->bs->countOthers($address));
    }



    /**
     * Test what countOther() returns when the user is logged in
     * and one friends (people on the watchlist) has bookmarked
     * the same address but made it private.
     *
     * @return void
     */
    public function testCountOthersWatchlistPrivate()
    {
        $uid  = $this->addUser();
        $address = 'http://example.org';

        //create other user and add main user to his watchlist
        $friendPublic1 = $this->addUser();
        $this->us->setCurrentUserId($friendPublic1);
        $this->us->setWatchStatus($uid);

        //create bookmarks for main user and other one
        $this->addBookmark($uid, $address, 0);
        $this->addBookmark($friendPublic1,  $address, 2);//2 is private

        //log main user in
        $this->us->setCurrentUserId($uid);

        $this->assertEquals(0, $this->bs->countOthers($address));
    }


    /**
     * Test what countOther() returns when the user is logged in
     * and friends (people on the watchlist) have bookmarked
     * and shared the same address.
     *
     * @return void
     */
    public function testCountOthersWatchlistComplex()
    {
        $uid  = $this->addUser();
        $address = 'http://example.org';
        //log user in
        $this->us->setCurrentUserId($uid);

        //setup users
        $otherPublic1   = $this->addUser();
        $otherPublic2   = $this->addUser();
        $otherShared1   = $this->addUser();
        $otherPrivate1  = $this->addUser();
        $friendPublic1  = $this->addUser();
        $friendShared1  = $this->addUser();
        $friendShared2  = $this->addUser();
        $friendPrivate1 = $this->addUser();
        $friendSharing1 = $this->addUser();

        //setup watchlists
        $us = SemanticScuttle_Service_Factory::get('User');
        $this->us->setCurrentUserId($friendPublic1);
        $us->setWatchStatus($uid);
        $this->us->setCurrentUserId($friendShared1);
        $us->setWatchStatus($uid);
        $this->us->setCurrentUserId($friendShared2);
        $us->setWatchStatus($uid);
        $this->us->setCurrentUserId($friendPrivate1);
        $us->setWatchStatus($uid);

        //back to login of main user
        $this->us->setCurrentUserId($uid);
        $us->setWatchStatus($friendSharing1);

        //add bookmarks
        $this->addBookmark($uid, $address, 0);
        $this->addBookmark($otherPublic1,   $address, 0);
        $this->addBookmark($otherPublic2,   $address, 0);
        $this->addBookmark($otherShared1,   $address, 1);
        $this->addBookmark($otherPrivate1,  $address, 2);
        $this->addBookmark($friendPublic1,  $address, 0);
        $this->addBookmark($friendShared1,  $address, 1);
        $this->addBookmark($friendShared2,  $address, 1);
        $this->addBookmark($friendPrivate1, $address, 2);
        //this user is on our watchlist, but we not on his
        $this->addBookmark($friendSharing1, $address, 1);

        //2 public
        //1 public (friend)
        //2 shared
        //-> 5
        $this->assertEquals(5, $this->bs->countOthers($address));
    }



    /**
     * Test what countOther() returns when multiple addresses are
     * passed to it and none of them exists.
     *
     * @return void
     */
    public function testCountOthersArrayNone()
    {
        $this->assertEquals(
            array('1' => 0, '2' => 0, '3' => 0),
            $this->bs->countOthers(array('1', '2', '3'))
        );
    }



    /**
     * Test what countOther() returns when multiple addresses are
     * passed to it and only one of them exists.
     *
     * @return void
     */
    public function testCountOthersArrayOneNone()
    {
        $uid  = $this->addUser();
        $uid2 = $this->addUser();
        $address1 = 'http://example.org/1';
        $address2 = 'http://example.org/2';
        $this->addBookmark($uid, $address1);
        $this->addBookmark($uid, $address2);
        $this->addBookmark($uid2, $address1);
        $this->assertEquals(
            array(
                $address1 => 1,
                $address2 => 0
            ),
            $this->bs->countOthers(
                array($address1, $address2)
            )
        );
    }



    /**
     * Test what countOther() returns when multiple addresses are passed
     * to it and both of them exist with different numbers for each.
     *
     * @return void
     */
    public function testCountOthersArrayTwoOne()
    {
        $uid  = $this->addUser();
        $uid2 = $this->addUser();
        $uid3 = $this->addUser();

        $address1 = 'http://example.org/1';
        $address2 = 'http://example.org/2';

        $this->addBookmark($uid, $address1);
        $this->addBookmark($uid, $address2);

        $this->addBookmark($uid2, $address1);
        $this->addBookmark($uid2, $address2);

        $this->addBookmark($uid3, $address1);

        $this->assertEquals(
            array(
                $address1 => 2,
                $address2 => 1
            ),
            $this->bs->countOthers(
                array($address1, $address2)
            )
        );
    }



    /**
     * Test private bookmarks
     *
     * @return void
     */
    public function testPrivateBookmarks()
    {
        $uid = $this->addUser();
        /* create private bookmark */
        $this->bs->addBookmark(
            'http://test', 'test', 'desc', 'note',
            2,//private
            array(), null, null, false, false, $uid
        );
        /* create public bookmark */
        $this->bs->addBookmark(
            'http://example.org', 'title', 'desc', 'priv',
            0,//public
            array(), null, null, false, false, $uid
        );

        $this->assertEquals(1, $this->bs->countBookmarks($uid, 'public'));
        $this->assertEquals(1, $this->bs->countBookmarks($uid, 'private'));
        $this->assertEquals(0, $this->bs->countBookmarks($uid, 'shared'));
        $this->assertEquals(2, $this->bs->countBookmarks($uid, 'all'));

        $this->us->setCurrentUserId($uid);
        $bookmarks = $this->bs->getBookmarks();
        // first record should be private bookmark
        $b0 = $bookmarks['bookmarks'][0];
        $this->assertEquals('test', $b0['bTitle']);
        // second record should be public bookmark
        $b0 = $bookmarks['bookmarks'][1];
        $this->assertEquals('title', $b0['bTitle']);

        // test non authenticated query
        $this->us->setCurrentUserId(null);
        $bookmarks = $this->bs->getBookmarks();
        // should only result in one link - public
        $b2 = $bookmarks['bookmarks'][0];
        $this->assertEquals('title', $b2['bTitle']);
        // there should be no second record
        $this->assertEquals(1,count($bookmarks['bookmarks']));

    }

}
?>
