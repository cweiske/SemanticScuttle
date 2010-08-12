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
    define('PHPUnit_MAIN_METHOD', 'Bookmark2TagTest::main');
}

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



    /**
     * Test getTagsForBookmark() when the bookmark has no tags
     *
     * @return void
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
     * @return void
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
     * @return void
     */
    public function testGetTagsForBookmarkThree()
    {
        $this->addBookmark(null, null, 0, array('forz', 'barz'));

        $bid = $this->addBookmark(null, null, 0, array());
        $this->b2ts->attachTags($bid, array('foo', 'bar', 'fuu'));

        $tags = $this->b2ts->getTagsForBookmark($bid);
        $this->assertType('array', $tags);
        $this->assertContains('foo', $tags);
        $this->assertContains('bar', $tags);
        $this->assertContains('fuu', $tags);
    }



    /**
     * Test getTagsForBookmarks() when no bookmarks have tags.
     *
     * @return void
     */
    public function testGetTagsForBookmarksNone()
    {
        $bid1 = $this->addBookmark(null, null, 0, array());
        $bid2 = $this->addBookmark(null, null, 0, array());

        $alltags = $this->b2ts->getTagsForBookmarks(
            array($bid1, $bid2)
        );
        $this->assertType('array', $alltags);
        $this->assertEquals(2, count($alltags));
        $this->assertType('array', $alltags[$bid1]);
        $this->assertType('array', $alltags[$bid2]);
        $this->assertEquals(0, count($alltags[$bid1]));
        $this->assertEquals(0, count($alltags[$bid2]));
    }



    /**
     * Test getTagsForBookmarks() when most bookmarks have tags.
     *
     * @return void
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
        $this->assertType('array', $alltags);
        foreach ($alltags as $bid => $btags) {
            $this->assertType('array', $btags);
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
}

if (PHPUnit_MAIN_METHOD == 'Bookmark2TagTest::main') {
    Bookmark2TagTest::main();
}
?>