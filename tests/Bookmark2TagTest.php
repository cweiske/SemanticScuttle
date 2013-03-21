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
 * Unit tests for the SemanticScuttle bookmark-tag combination service.
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Benjamin Huynh-Kim-Bang <mensonge@users.sourceforge.net>
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @author   Eric Dane <ericdane@users.sourceforge.net>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */
class Bookmark2TagTest extends TestBase
{
    protected $us;
    protected $bs;
    protected $ts;
    protected $tts;


    /**
     * Create a bookmark. Like addBookmark(), just with other paramter order
     * to make some tests in that class easier to write.
     *
     * @param integer $user    User ID the bookmark shall belong
     * @param array   $tags    Array of tags to attach. If "null" is given,
     *                         it will automatically be "unittest"
     * @param string  $date    strtotime-compatible string
     * @param string  $title   Bookmark title
     *
     * @return integer ID of bookmark
     */
    protected function addTagBookmark($user, $tags, $date = null, $title = null)
    {
        return $this->addBookmark(
            $user, null, 0, $tags, $title, $date
        );
    }



    protected function setUp()
    {
        $this->us = SemanticScuttle_Service_Factory::get('User');
        $this->us->deleteAll();
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



    public function testAttachTagsWithoutTagsAddsSystemUnfiled()
    {
        $bid = $this->addBookmark(null, null, 0, array());
        $this->assertEquals(
            array('system:unfiled'),
            $this->b2ts->getTagsForBookmark($bid, true)
        );
    }

    public function testAttachTagsWithArrayWithEmptyStringAddsSystemUnfiled()
    {
        $bid = $this->addBookmark(null, null, 0, array(''));
        $this->assertEquals(
            array('system:unfiled'),
            $this->b2ts->getTagsForBookmark($bid, true)
        );
    }

    public function testAttachTagsWithEmptyStringAddsSystemUnfiled()
    {
        $originalDisplayErros = ini_get('display_errors');
        $originalErrorReporting = ini_get('error_reporting');
        ini_set('display_errors', 1);
        error_reporting(E_ALL);
        $bid = $this->addBookmark(null, null, 0, '');
        $this->assertEquals(
            array('system:unfiled'),
            $this->b2ts->getTagsForBookmark($bid, true)
        );
        ini_set('display_errors', $originalDisplayErros);
        error_reporting($originalErrorReporting);
    }

    public function testAttachTagsWithSomeEmptyTags()
    {
        $bid = $this->addBookmark(null, null, 0, array());
        $this->b2ts->attachTags($bid, array('foo', '', 'bar', 'baz'));
        $this->assertEquals(
            array('foo', 'bar', 'baz'),
            $this->b2ts->getTagsForBookmark($bid)
        );
    }

    /**
     * Test getTagsForBookmark() when the bookmark has no tags
     *
     * @covers SemanticScuttle_Service_Bookmark2Tag::getTagsForBookmark
     */
    public function testGetTagsForBookmarkNone()
    {
        $this->addBookmark(null, null, 0, array('forz', 'barz'));

        $bid = $this->addBookmark(null, null, 0, array());
        $this->assertEquals(
            array(),
            $this->b2ts->getTagsForBookmark($bid)
        );
    }



    /**
     * Test getTagsForBookmark() when the bookmark has one tag
     *
     * @covers SemanticScuttle_Service_Bookmark2Tag::getTagsForBookmark
     */
    public function testGetTagsForBookmarkOne()
    {
        $this->addBookmark(null, null, 0, array('forz', 'barz'));

        $bid = $this->addBookmark(null, null, 0, array());
        $this->b2ts->attachTags($bid, array('foo'));
        $this->assertEquals(
            array('foo'),
            $this->b2ts->getTagsForBookmark($bid)
        );
    }



    /**
     * Test getTagsForBookmark() when the bookmark has three tags
     *
     * @covers SemanticScuttle_Service_Bookmark2Tag::getTagsForBookmark
     */
    public function testGetTagsForBookmarkThr()
    {
        $this->addBookmark(null, null, 0, array('forz', 'barz'));

        $bid = $this->addBookmark(null, null, 0, array());
        $this->b2ts->attachTags($bid, array('foo', 'bar', 'fuu'));

        $tags = $this->b2ts->getTagsForBookmark($bid);
        $this->assertInternalType('array', $tags);
        $this->assertContains('foo', $tags);
        $this->assertContains('bar', $tags);
        $this->assertContains('fuu', $tags);
    }



    /**
     * Test getTagsForBookmarks() when no bookmarks have tags.
     *
     * @covers SemanticScuttle_Service_Bookmark2Tag::getTagsForBookmarks
     */
    public function testGetTagsForBookmarksNone()
    {
        $bid1 = $this->addBookmark(null, null, 0, array());
        $bid2 = $this->addBookmark(null, null, 0, array());

        $alltags = $this->b2ts->getTagsForBookmarks(
            array($bid1, $bid2)
        );
        $this->assertInternalType('array', $alltags);
        $this->assertEquals(2, count($alltags));
        $this->assertInternalType('array', $alltags[$bid1]);
        $this->assertInternalType('array', $alltags[$bid2]);
        $this->assertEquals(0, count($alltags[$bid1]));
        $this->assertEquals(0, count($alltags[$bid2]));
    }



    /**
     * Test getTagsForBookmarks() when most bookmarks have tags.
     *
     * @covers SemanticScuttle_Service_Bookmark2Tag::getTagsForBookmarks
     */
    public function testGetTagsForBookmarksMost()
    {
        $bid1 = $this->addBookmark(null, null, 0, array());
        $this->b2ts->attachTags($bid1, array('foo', 'bar', 'fuu'));

        $bid2 = $this->addBookmark(null, null, 0, array());
        $this->b2ts->attachTags($bid2, array('foo', 'bar2', 'fuu2'));

        $bid3 = $this->addBookmark(null, null, 0, array());
        $this->b2ts->attachTags($bid3, array('foo', 'bar2', 'fuu3'));

        $bid4 = $this->addBookmark(null, null, 0, array());
        //no tags

        //bookmark that does not get queried
        //http://sourceforge.net/projects/semanticscuttle/forums/forum/759510/topic/3752670
        $bid5 = $this->addBookmark(null, null, 0, array());
        $this->b2ts->attachTags($bid5, array('foo', 'bar2', 'fuu5'));


        $alltags = $this->b2ts->getTagsForBookmarks(
            array($bid1, $bid2, $bid3, $bid4)
        );
        $this->assertInternalType('array', $alltags);
        foreach ($alltags as $bid => $btags) {
            $this->assertInternalType('array', $btags);
            if ($bid == $bid1) {
                $this->assertEquals(3, count($btags));
                $this->assertContains('foo', $btags);
                $this->assertContains('bar', $btags);
                $this->assertContains('fuu', $btags);
            } else if ($bid == $bid2) {
                $this->assertEquals(3, count($btags));
                $this->assertContains('foo', $btags);
                $this->assertContains('bar2', $btags);
                $this->assertContains('fuu2', $btags);
            } else if ($bid == $bid3) {
                $this->assertEquals(3, count($btags));
                $this->assertContains('foo', $btags);
                $this->assertContains('bar2', $btags);
                $this->assertContains('fuu3', $btags);
            } else if ($bid == $bid4) {
                $this->assertEquals(0, count($btags));
            } else {
                $this->assertTrue(false, 'Unknown bookmark id');
            }
        }
    }



    /**
     * Fetch the most popular tags in descending order
     *
     * @covers SemanticScuttle_Service_Bookmark2Tag::getPopularTags
     */
    public function testGetPopularTagsOrder()
    {
        $user = $this->addUser();
        $this->addTagBookmark($user, array('one', 'two'));
        $this->addTagBookmark($user, array('one', 'thr'));
        $this->addTagBookmark($user, array('one', 'two'));

        $arTags = $this->b2ts->getPopularTags();
        $this->assertInternalType('array', $arTags);
        $this->assertEquals(3, count($arTags));

        $this->assertInternalType('array', $arTags[0]);

        $this->assertEquals(
            array(
                array('tag' => 'one', 'bCount' => '3'),
                array('tag' => 'two', 'bCount' => '2'),
                array('tag' => 'thr', 'bCount' => '1')
            ),
            $arTags
        );
    }



    /**
     * @covers SemanticScuttle_Service_Bookmark2Tag::getPopularTags
     */
    public function testGetPopularTagsLimit()
    {
        $user = $this->addUser();
        $this->addTagBookmark($user, array('one', 'two'));
        $this->addTagBookmark($user, array('one', 'thr'));
        $this->addTagBookmark($user, array('one', 'two'));

        $arTags = $this->b2ts->getPopularTags();
        $this->assertInternalType('array', $arTags);
        $this->assertEquals(3, count($arTags));

        $arTags = $this->b2ts->getPopularTags(null, 2);
        $this->assertInternalType('array', $arTags);
        $this->assertEquals(2, count($arTags));
        $this->assertEquals(
            array(
                array('tag' => 'one', 'bCount' => '3'),
                array('tag' => 'two', 'bCount' => '2'),
            ),
            $arTags
        );

        $arTags = $this->b2ts->getPopularTags(null, 1);
        $this->assertInternalType('array', $arTags);
        $this->assertEquals(1, count($arTags));
        $this->assertEquals(
            array(
                array('tag' => 'one', 'bCount' => '3'),
            ),
            $arTags
        );
    }



    /**
     * @covers SemanticScuttle_Service_Bookmark2Tag::getPopularTags
     */
    public function testGetPopularTagsDays()
    {
        $user = $this->addUser();
        $this->addTagBookmark($user, array('one', 'two'), 'now');
        $this->addTagBookmark($user, array('one', 'thr'), 'now');
        $this->addTagBookmark($user, array('one', 'two'), '-1 day -1 hour');
        $this->addTagBookmark($user, array('one', 'thr'), '-3 days -1 hour');

        $arTags = $this->b2ts->getPopularTags(null, 10, null, 1);
        $this->assertInternalType('array', $arTags);
        $this->assertEquals(3, count($arTags));
        $this->assertContains(array('tag' => 'one', 'bCount' => '2'), $arTags);
        $this->assertContains(array('tag' => 'two', 'bCount' => '1'), $arTags);
        $this->assertContains(array('tag' => 'thr', 'bCount' => '1'), $arTags);

        $arTags = $this->b2ts->getPopularTags(null, 10, null, 2);
        $this->assertInternalType('array', $arTags);
        $this->assertEquals(3, count($arTags));
        $this->assertEquals(
            array(
                array('tag' => 'one', 'bCount' => '3'),
                array('tag' => 'two', 'bCount' => '2'),
                array('tag' => 'thr', 'bCount' => '1'),
            ),
            $arTags
        );

        $arTags = $this->b2ts->getPopularTags(null, 10, null, 5);
        $this->assertInternalType('array', $arTags);
        $this->assertEquals(3, count($arTags));
        $this->assertContains(array('tag' => 'one', 'bCount' => '4'), $arTags);
        $this->assertContains(array('tag' => 'two', 'bCount' => '2'), $arTags);
        $this->assertContains(array('tag' => 'thr', 'bCount' => '2'), $arTags);
    }

    /**
     * @covers SemanticScuttle_Service_Bookmark2Tag::getPopularTags
     */
    public function testGetPopularTagsBeginsWith()
    {
        $user = $this->addUser();
        $this->addTagBookmark($user, array('one', 'two'));
        $this->addTagBookmark($user, array('one', 'thr'));
        $this->addTagBookmark($user, array('one', 'two'));
        $this->addTagBookmark($user, array('one', 'thr'));

        $arTags = $this->b2ts->getPopularTags(null, 10, null, null, 'o');
        $this->assertEquals(1, count($arTags));
        $this->assertContains(array('tag' => 'one', 'bCount' => '4'), $arTags);

        $arTags = $this->b2ts->getPopularTags(null, 10, null, null, 'tw');
        $this->assertEquals(1, count($arTags));
        $this->assertContains(array('tag' => 'two', 'bCount' => '2'), $arTags);

        $arTags = $this->b2ts->getPopularTags(null, 10, null, null, 't');
        $this->assertEquals(2, count($arTags));
        $this->assertContains(array('tag' => 'two', 'bCount' => '2'), $arTags);
        $this->assertContains(array('tag' => 'thr', 'bCount' => '2'), $arTags);
    }



    /**
     * @covers SemanticScuttle_Service_Bookmark2Tag::getPopularTags
     */
    public function testGetPopularTagsExcludesSystemTags()
    {
        $user = $this->addUser();
        $this->addTagBookmark($user, array('one', 'system:test'));
        $this->addTagBookmark($user, array('one', 'system:unittest'));
        $this->addTagBookmark($user, array('one', 'sys:unittest'));

        $arTags = $this->b2ts->getPopularTags();
        $this->assertInternalType('array', $arTags);
        $this->assertEquals(2, count($arTags));
        $this->assertEquals(
            array(
                array('tag' => 'one', 'bCount' => '3'),
                array('tag' => 'sys:unittest', 'bCount' => '1'),
            ),
            $arTags
        );
    }



    /**
     * @covers SemanticScuttle_Service_Bookmark2Tag::getPopularTags
     */
    public function testGetPopularTagsUserTags()
    {
        $user1 = $this->addUser();
        $user2 = $this->addUser();
        $user3 = $this->addUser();
        $this->addTagBookmark($user1, array('one'));
        $this->addTagBookmark($user2, array('one', 'two'));
        $this->addTagBookmark($user2, array('two'));
        $this->addTagBookmark($user3, array('one', 'thr'));

        $arTags = $this->b2ts->getPopularTags($user1);
        $this->assertEquals(1, count($arTags));
        $this->assertEquals(
            array(
                array('tag' => 'one', 'bCount' => '1'),
            ),
            $arTags
        );

        $arTags = $this->b2ts->getPopularTags($user2);
        $this->assertEquals(2, count($arTags));
        $this->assertEquals(
            array(
                array('tag' => 'two', 'bCount' => '2'),
                array('tag' => 'one', 'bCount' => '1'),
            ),
            $arTags
        );

        $arTags = $this->b2ts->getPopularTags(array($user2, $user3));
        $this->assertEquals(3, count($arTags));
        $this->assertContains(array('tag' => 'one', 'bCount' => '2'), $arTags);
        $this->assertContains(array('tag' => 'two', 'bCount' => '2'), $arTags);
        $this->assertContains(array('tag' => 'thr', 'bCount' => '1'), $arTags);
    }



    /**
     * This may happen when the method is called with a problematic user array.
     * In that case we may not generate invalid SQL or so.
     *
     * @covers SemanticScuttle_Service_Bookmark2Tag::getPopularTags
     */
    public function testGetPopularTagsUserArrayWithNull()
    {
        $user1 = $this->addUser();
        $this->addTagBookmark($user1, array('one'));

        $arTags = $this->b2ts->getPopularTags(array(null));
        $this->assertEquals(0, count($arTags));
    }



    /**
     * @covers SemanticScuttle_Service_Bookmark2Tag::getPopularTags
     */
    public function testGetPopularTagsPublicOnlyNoUser()
    {
        $user1 = $this->addUser();
        $this->addBookmark($user1, null, 0, array('one'));
        $this->addBookmark($user1, null, 1, array('one', 'two'));
        $this->addBookmark($user1, null, 2, array('thr'));

        $arTags = $this->b2ts->getPopularTags();
        $this->assertEquals(1, count($arTags));
        $this->assertEquals(
            array(
                array('tag' => 'one', 'bCount' => '1'),
            ),
            $arTags
        );
    }

    /**
     * @covers SemanticScuttle_Service_Bookmark2Tag::getPopularTags
     */
    public function testGetPopularTagsPublicOnlySingleUser()
    {
        $user1 = $this->addUser();
        $this->addBookmark($user1, null, 0, array('one'));
        $this->addBookmark($user1, null, 1, array('one', 'two'));
        $this->addBookmark($user1, null, 2, array('thr'));

        $arTags = $this->b2ts->getPopularTags($user1);
        $this->assertEquals(1, count($arTags));
        $this->assertEquals(
            array(
                array('tag' => 'one', 'bCount' => '1'),
            ),
            $arTags
        );
    }

    /**
     * @covers SemanticScuttle_Service_Bookmark2Tag::getPopularTags
     */
    public function testGetPopularTagsPublicOnlySeveralUsers()
    {
        $user1 = $this->addUser();
        $user2 = $this->addUser();
        $this->addBookmark($user1, null, 0, array('one'));
        $this->addBookmark($user1, null, 1, array('one', 'two'));
        $this->addBookmark($user1, null, 2, array('thr'));
        $this->addBookmark($user2, null, 0, array('fou'));
        $this->addBookmark($user2, null, 1, array('fiv'));
        $this->addBookmark($user2, null, 2, array('six'));

        $arTags = $this->b2ts->getPopularTags(array($user1, $user2));
        $this->assertEquals(2, count($arTags));
        $this->assertContains(array('tag' => 'one', 'bCount' => '1'), $arTags);
        $this->assertContains(array('tag' => 'fou', 'bCount' => '1'), $arTags);
    }

    /**
     * @covers SemanticScuttle_Service_Bookmark2Tag::getPopularTags
     */
    public function testGetPopularTagsUserPrivatesWhenLoggedIn()
    {
        $user1 = $this->addUser();
        $this->addBookmark($user1, null, 0, array('one'));
        $this->addBookmark($user1, null, 1, array('one', 'two'));
        $this->addBookmark($user1, null, 2, array('thr'));

        $arTags = $this->b2ts->getPopularTags($user1, 10, $user1);
        $this->assertEquals(3, count($arTags));
        $this->assertContains(array('tag' => 'one', 'bCount' => '2'), $arTags);
        $this->assertContains(array('tag' => 'two', 'bCount' => '1'), $arTags);
        $this->assertContains(array('tag' => 'thr', 'bCount' => '1'), $arTags);
    }

    /**
     * Should return the logged on user's public, protected and private tags
     * as well as public ones of other specified users.
     *
     * @covers SemanticScuttle_Service_Bookmark2Tag::getPopularTags
     */
    public function testGetPopularTagsUserPrivatesAndOthersWhenLoggedIn()
    {
        $user1 = $this->addUser();
        $user2 = $this->addUser();
        $this->addBookmark($user1, null, 0, array('one'));
        $this->addBookmark($user1, null, 1, array('one', 'two'));
        $this->addBookmark($user1, null, 2, array('thr'));
        $this->addBookmark($user2, null, 0, array('fou'));
        $this->addBookmark($user2, null, 1, array('fiv'));
        $this->addBookmark($user2, null, 2, array('six'));

        $arTags = $this->b2ts->getPopularTags(array($user2, $user1), 10, $user1);
        $this->assertContains(array('tag' => 'one', 'bCount' => '2'), $arTags);
        $this->assertContains(array('tag' => 'two', 'bCount' => '1'), $arTags);
        $this->assertContains(array('tag' => 'thr', 'bCount' => '1'), $arTags);
        $this->assertContains(array('tag' => 'fou', 'bCount' => '1'), $arTags);
        $this->assertEquals(4, count($arTags));
    }


    /**
     * @covers SemanticScuttle_Service_Bookmark2Tag::getAdminTags
     */
    public function testGetAdminTags()
    {
        $admin1 = $this->addUser('admin1');
        $admin2 = $this->addUser('admin2');
        $user1  = $this->addUser();
        $this->addBookmark($admin1, null, 0, array('admintag', 'admintag1'));
        $this->addBookmark($admin2, null, 0, array('admintag', 'admintag2'));
        $this->addBookmark($user1, null, 0, array('usertag'));

        $GLOBALS['admin_users'] = array('admin1', 'admin2');

        $arTags = $this->b2ts->getAdminTags(4);
        $this->assertEquals(3, count($arTags));
        $this->assertContains(array('tag' => 'admintag', 'bCount' => '2'), $arTags);
        $this->assertContains(array('tag' => 'admintag1', 'bCount' => '1'), $arTags);
        $this->assertContains(array('tag' => 'admintag2', 'bCount' => '1'), $arTags);
    }

    /**
     * @covers SemanticScuttle_Service_Bookmark2Tag::getAdminTags
     */
    public function testGetAdminTagsBeginsWith()
    {
        $admin1 = $this->addUser('admin1');
        $this->addBookmark($admin1, null, 0, array('admintag', 'admintag1'));
        $this->addBookmark($admin1, null, 0, array('tester', 'testos'));

        $GLOBALS['admin_users'] = array('admin1');

        $arTags = $this->b2ts->getAdminTags(4, null, null, 'test');
        $this->assertEquals(2, count($arTags));
        $this->assertContains(array('tag' => 'tester', 'bCount' => '1'), $arTags);
        $this->assertContains(array('tag' => 'testos', 'bCount' => '1'), $arTags);
    }



    /**
     * @covers SemanticScuttle_Service_Bookmark2Tag::getContactTags
     */
    public function testGetContactTagsWatchlistOnly()
    {
        $user1 = $this->addUser();
        $user2 = $this->addUser();
        $user3 = $this->addUser();
        $this->us->setCurrentUserId($user1);
        $this->us->setWatchStatus($user2);
        //user1 watches user2 now

        $this->addBookmark($user1, null, 0, array('usertag', 'usertag1'));
        $this->addBookmark($user2, null, 0, array('usertag', 'usertag2'));
        $this->addBookmark($user3, null, 0, array('usertag', 'usertag3'));

        $arTags = $this->b2ts->getContactTags($user1, 10);
        $this->assertEquals(2, count($arTags));
        $this->assertContains(array('tag' => 'usertag', 'bCount' => '1'), $arTags);
        $this->assertContains(array('tag' => 'usertag2', 'bCount' => '1'), $arTags);
    }

    /**
     * @covers SemanticScuttle_Service_Bookmark2Tag::getContactTags
     */
    public function testGetContactTagsIncludingUser()
    {
        $user1 = $this->addUser();
        $user2 = $this->addUser();
        $user3 = $this->addUser();
        $this->us->setCurrentUserId($user1);
        $this->us->setWatchStatus($user2);
        //user1 watches user2 now

        $this->addBookmark($user1, null, 0, array('usertag', 'usertag1'));
        $this->addBookmark($user2, null, 0, array('usertag', 'usertag2'));
        $this->addBookmark($user3, null, 0, array('usertag', 'usertag3'));

        $arTags = $this->b2ts->getContactTags($user1, 10, $user1);
        $this->assertEquals(3, count($arTags));
        $this->assertContains(array('tag' => 'usertag', 'bCount' => '2'), $arTags);
        $this->assertContains(array('tag' => 'usertag1', 'bCount' => '1'), $arTags);
        $this->assertContains(array('tag' => 'usertag2', 'bCount' => '1'), $arTags);
    }

    /**
     * @covers SemanticScuttle_Service_Bookmark2Tag::getContactTags
     */
    public function testGetContactTagsBeginsWith()
    {
        $user1 = $this->addUser();
        $this->addBookmark($user1, null, 0, array('usertag', 'usertag1'));
        $this->addBookmark($user1, null, 0, array('usable', 'undefined'));
        $this->addBookmark($user1, null, 0, array('fußbad', 'usable'));

        $arTags = $this->b2ts->getContactTags($user1, 10, $user1, null, 'user');
        $this->assertEquals(2, count($arTags));
        $this->assertContains(array('tag' => 'usertag', 'bCount' => '1'), $arTags);
        $this->assertContains(array('tag' => 'usertag1', 'bCount' => '1'), $arTags);

        $arTags = $this->b2ts->getContactTags($user1, 10, $user1, null, 'us');
        $this->assertEquals(3, count($arTags));
        $this->assertContains(array('tag' => 'usertag', 'bCount' => '1'), $arTags);
        $this->assertContains(array('tag' => 'usertag1', 'bCount' => '1'), $arTags);
        $this->assertContains(array('tag' => 'usable', 'bCount' => '2'), $arTags);
    }

    public function testHasTag()
    {
        $bid = $this->addBookmark(null, null, 0, array('foo'));

        $this->assertTrue($this->b2ts->hasTag($bid, 'foo'));
        $this->assertFalse($this->b2ts->hasTag($bid, 'bar'));

    }
}
?>
