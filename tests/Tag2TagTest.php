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
 * Unit tests for the SemanticScuttle tag2tag service.
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Benjamin Huynh-Kim-Bang <mensonge@users.sourceforge.net>
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @author   Eric Dane <ericdane@users.sourceforge.net>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */
class Tag2TagTest extends TestBase
{
    protected $us;
    protected $bs;
    protected $b2ts;
    protected $tts;


    protected function setUp()
    {
        $this->us =SemanticScuttle_Service_Factory::get('User');
        $this->us->deleteAll();
        $this->addUser();
        $this->bs =SemanticScuttle_Service_Factory::get('Bookmark');
        $this->bs->deleteAll();
        $this->b2ts =SemanticScuttle_Service_Factory::get('Bookmark2Tag');
        $this->b2ts->deleteAll();
        $this->tts =SemanticScuttle_Service_Factory::get('Tag2Tag');
        $this->tts->deleteAll();
        $this->tsts =SemanticScuttle_Service_Factory::get('TagStat');
        $this->tsts->deleteAll();
    }

    public function testManipulateTag2TagRelationsOfInclusion()
    {
        $tts = $this->tts;

        $tts->addLinkedTags('a', 'b', '>', 1);
        $tts->addLinkedTags('a', 'c', '>', 1);
        $tts->addLinkedTags('a', 'd', '>', 20);
        $tts->addLinkedTags('b', 'd', '>', 1);
        $tts->addLinkedTags('d', 'e', '>', 1);
        $tts->addLinkedTags('d', 'e', '>', 20);
        $tts->addLinkedTags('f', 'g', '>', 20);

        // basic test


        $links = $tts->getLinks(1);
        $this->assertEquals(4, count($links));

        $allLinkedTags = $tts->getAllLinkedTags('e', '>', 1);
        $this->assertEquals(array(), $allLinkedTags);

        $allLinkedTags = $tts->getAllLinkedTags('d', '>', 1);
        $this->assertEquals(array('e'), $allLinkedTags);

        $allLinkedTags = $tts->getAllLinkedTags('b', '>', 1);
        $this->assertEquals(array('d', 'e'), $allLinkedTags);
        $this->assertEquals(2, sizeof($allLinkedTags));
        $this->assertTrue(in_array('d', $allLinkedTags));
        $this->assertTrue(in_array('e', $allLinkedTags));

        $allLinkedTags = $tts->getAllLinkedTags('a', '>', 1);
        $this->assertEquals(4, sizeof($allLinkedTags));
        $this->assertTrue(in_array('b', $allLinkedTags));
        $this->assertTrue(in_array('c', $allLinkedTags));
        $this->assertTrue(in_array('d', $allLinkedTags));
        $this->assertTrue(in_array('e', $allLinkedTags));


        // warning: we add recursive link
        $tts->addLinkedTags('b', 'a', '>', 1);

        $allLinkedTags = $tts->getAllLinkedTags('a', '>', 1);
        $this->assertEquals(4, sizeof($allLinkedTags));
        //$this->assertTrue(in_array('a', $allLinkedTags));
        $this->assertTrue(in_array('b', $allLinkedTags));
        $this->assertTrue(in_array('c', $allLinkedTags));
        $this->assertTrue(in_array('d', $allLinkedTags));
        $this->assertTrue(in_array('e', $allLinkedTags));

        $orphewTags = $tts->getOrphewTags('>', 1);
        $this->assertEquals(0, sizeof($orphewTags));
        $orphewTags = $tts->getOrphewTags('>', 20);
        $this->assertEquals(2, sizeof($orphewTags));
        $this->assertSame('a', $orphewTags[0]['tag']);
        $this->assertSame('f', $orphewTags[1]['tag']);
        $orphewTags = $tts->getOrphewTags('>');
        $this->assertEquals(1, sizeof($orphewTags));
        $this->assertSame('f', $orphewTags[0]['tag']);

        $linkedTags = $tts->getLinkedTags('a', '>');
        $this->assertSame(array('b', 'c', 'd'), $linkedTags);
        $linkedTags = $tts->getLinkedTags('a', '>', 1);
        $this->assertSame(array('b', 'c'), $linkedTags);
        $tts->removeLinkedTags('a', 'b', '>', 1);
        $linkedTags = $tts->getLinkedTags('a', '>', 1);
        $this->assertSame(array('c'), $linkedTags);
        $tts->removeLinkedTags('a', 'c', '>', 1);
        $linkedTags = $tts->getLinkedTags('a', '>', 1);
        $this->assertEquals(0, sizeof($linkedTags));
    }

    public function testManipulateTag2TagRelationsOfSynonym()
    {
        $tts = $this->tts;

        $tts->addLinkedTags('a', 'b', '>', 1);
        $tts->addLinkedTags('b', 'c', '>', 1);
        $tts->addLinkedTags('b', 'd', '=', 1);
        $tts->addLinkedTags('d', 'e', '=', 1);
        $tts->addLinkedTags('d', 'f', '=', 1);
        $tts->addLinkedTags('e', 'g', '>', 1);

        $linkedTags = $tts->getLinkedTags('a', '>', 1);
        $this->assertSame(array('b'), $linkedTags);

        $linkedTags = $tts->getLinkedTags('a', '=', 1);
        $this->assertSame(array(), $linkedTags);

        $linkedTags = $tts->getLinkedTags('b', '=', 1);
        $this->assertSame(array('d'), $linkedTags);

        $linkedTags = $tts->getLinkedTags('d', '=', 1);
        $this->assertEquals(3, sizeof($linkedTags));
        $this->assertTrue(in_array('b', $linkedTags)); // '=' is bijective
        $this->assertTrue(in_array('e', $linkedTags));
        $this->assertTrue(in_array('f', $linkedTags));

        $linkedTags = $tts->getLinkedTags('f', '=', 1);
        $this->assertEquals(1, sizeof($linkedTags));
        $this->assertTrue(in_array('d', $linkedTags)); // '=' is bijective

        // test allLinkTags (with inference)
        $allLinkedTags = $tts->getAllLinkedTags('a', '=', 1);
        $this->assertEquals(0, sizeof($allLinkedTags));

        $allLinkedTags = $tts->getAllLinkedTags('b', '=', 1);
        $this->assertEquals(3, sizeof($allLinkedTags));
        $this->assertTrue(in_array('d', $allLinkedTags));
        $this->assertTrue(in_array('e', $allLinkedTags));
        $this->assertTrue(in_array('f', $allLinkedTags));

        $allLinkedTags = $tts->getAllLinkedTags('f', '>', 1);
        $this->assertEquals(5, sizeof($allLinkedTags));
        $this->assertTrue(in_array('b', $allLinkedTags));
        $this->assertTrue(in_array('d', $allLinkedTags));
        $this->assertTrue(in_array('e', $allLinkedTags));
        $this->assertTrue(in_array('c', $allLinkedTags));
        $this->assertTrue(in_array('g', $allLinkedTags));

        $allLinkedTags = $tts->getAllLinkedTags('a', '>', 1);
        $this->assertEquals(6, sizeof($allLinkedTags));
        $this->assertTrue(in_array('b', $allLinkedTags));
        $this->assertTrue(in_array('c', $allLinkedTags));
        $this->assertTrue(in_array('d', $allLinkedTags));
        $this->assertTrue(in_array('e', $allLinkedTags));
        $this->assertTrue(in_array('f', $allLinkedTags));
        $this->assertTrue(in_array('g', $allLinkedTags));

        $tts->addLinkedTags('g', 'h', '>', 1);
        $tts->addLinkedTags('i', 'h', '=', 1);
        $tts->addLinkedTags('j', 'f', '>', 1);

        $allLinkedTags = $tts->getAllLinkedTags('j', '>', 1);
        $this->assertEquals(8, sizeof($allLinkedTags));
        $this->assertTrue(in_array('b', $allLinkedTags));
        $this->assertTrue(in_array('c', $allLinkedTags));
        $this->assertTrue(in_array('d', $allLinkedTags));
        $this->assertTrue(in_array('e', $allLinkedTags));
        $this->assertTrue(in_array('f', $allLinkedTags));
        $this->assertTrue(in_array('g', $allLinkedTags));
        $this->assertTrue(in_array('h', $allLinkedTags));
        $this->assertTrue(in_array('i', $allLinkedTags));

        // complex case: test cycle
        $tts->addLinkedTags('g', 'a', '>', 1);
        $allLinkedTags = $tts->getAllLinkedTags('b', '>', 1);
        $this->assertEquals(8, sizeof($allLinkedTags));
        $this->assertTrue(in_array('a', $allLinkedTags));
        $this->assertTrue(in_array('c', $allLinkedTags));
        $this->assertTrue(in_array('d', $allLinkedTags));
        $this->assertTrue(in_array('e', $allLinkedTags));
        $this->assertTrue(in_array('f', $allLinkedTags));
        $this->assertTrue(in_array('g', $allLinkedTags));
        $this->assertTrue(in_array('h', $allLinkedTags));
        $this->assertTrue(in_array('i', $allLinkedTags));

    }

    // Test function that select the best tags to display?
    public function testViewTag2TagRelations()
    {
        $tts = $this->tts;

        $tts->addLinkedTags('a', 'b', '>', 1);
        $tts->addLinkedTags('c', 'd', '>', 1);
        $tts->addLinkedTags('d', 'e', '>', 1);
        $tts->addLinkedTags('f', 'g', '>', 1);
        $tts->addLinkedTags('f', 'h', '>', 1);
        $tts->addLinkedTags('f', 'i', '>', 1);

        $orphewTags = $tts->getOrphewTags('>', 1);
        $this->assertEquals(3, sizeof($orphewTags));
        $this->assertSame('a', $orphewTags[0]['tag']);
        $this->assertSame('c', $orphewTags[1]['tag']);
        $this->assertSame('f', $orphewTags[2]['tag']);

        // with limit
        $orphewTags = $tts->getOrphewTags('>', 1, 2);
        $this->assertEquals(2, sizeof($orphewTags));
        $this->assertSame('a', $orphewTags[0]['tag']);
        $this->assertSame('c', $orphewTags[1]['tag']);

        // with sorting
        $orphewTags = $tts->getOrphewTags('>', 1, 2, 'nb'); // nb descendants
        $this->assertEquals(2, sizeof($orphewTags));
        $this->assertSame('f', $orphewTags[0]['tag']);
        $this->assertSame('c', $orphewTags[1]['tag']);

        $orphewTags = $tts->getOrphewTags('>', 1, 1, 'depth');
        $this->assertEquals(1, sizeof($orphewTags));
        $this->assertSame('c', $orphewTags[0]['tag']);

        $orphewTags = $tts->getOrphewTags('>', 1, null, 'nbupdate');
        $this->assertEquals(3, sizeof($orphewTags));
        $this->assertSame('f', $orphewTags[0]['tag']);
        $this->assertSame('c', $orphewTags[1]['tag']);
        $this->assertSame('a', $orphewTags[2]['tag']);

    }

    public function testAddLinkedTagsThroughBookmarking()
    {
        $bs   = $this->bs;
        $tags = array('a>b', 'b>c', 'a>d>e', 'a>a', 'a', 'r=s', 's=t=u');
        $uid  = $this->addUser();
        $bs->addBookmark(
            "http://google.com", "title", "description", 'note',
            0, $tags, null, null, false, false,
            $uid
        );
        $bookmark = $bs->getBookmarkByAddress("http://google.com");

        $b2ts = $this->b2ts;
        $savedTags = $b2ts->getTagsForBookmark(intval($bookmark['bId']));
        $this->assertEquals(6, sizeof($savedTags));
        $this->assertContains('b', $savedTags);
        $this->assertContains('c', $savedTags);
        $this->assertContains('e', $savedTags);
        $this->assertContains('a', $savedTags);
        $this->assertContains('r', $savedTags);
        $this->assertContains('s', $savedTags);

        $tts = $this->tts;
        $linkedTags = $tts->getLinkedTags('a', '>', $uid);
        $this->assertEquals(2, count($linkedTags));
        $this->assertInternalType('string', $linkedTags[0]);
        $this->assertSame('b', $linkedTags[0]);
        $this->assertInternalType('string', $linkedTags[1]);
        $this->assertSame('d', $linkedTags[1]);

        $linkedTags = $tts->getLinkedTags('b', '>', $uid);
        $this->assertEquals(1, count($linkedTags));
        $this->assertSame('c', $linkedTags[0]);
        $this->assertTrue($tts->existsLinkedTags('d', 'e', '>', $uid));
        $this->assertFalse($tts->existsLinkedTags('e', 'd', '>', $uid));
    }

    public function testSearchThroughLinkedTags()
    {
        $tts = $this->tts;
        $bs = $this->bs;

        $tts->addLinkedTags('aa', 'bb', '>', 1);

        $tags = array('aa>bb>cc', 'dd');
        $bs->addBookmark(
            "web1.com", "B1", "description", 'note', 0,
            $tags, null, null, false, false, 1
        );
        $tags = array('bb>gg', 'ee>ff');
        $bs->addBookmark(
            "web2.com", "B2", "description", 'note', 0,
            $tags, null, null, false, false, 1
        );
        $tags = array('ee=ii');
        $bs->addBookmark(
            "web3.com", "B3", "description", 'note', 0,
            $tags, null, null, false, false, 1
        );

        // Query format:
        // $bs->getBookmarks($start = 0, $perpage = NULL, $user = NULL, $tags = NULL, $terms = NULL, $sortOrder = NULL, $watched = NULL, $startdate = NULL, $enddate = NULL, $hash = NULL);

        // basic queries
        $results = $bs->getBookmarks(0, NULL, 1, 'dd');
        $this->assertSame(1, intval($results['total']));
        $this->assertSame('B1', $results['bookmarks'][0]['bTitle']);

        $results = $bs->getBookmarks(0, NULL, 1, 'cc');
        $this->assertSame(1, intval($results['total']));
        $this->assertSame('B1', $results['bookmarks'][0]['bTitle']);

        //advanced queries
        $results = $bs->getBookmarks(0, NULL, 1, 'aa', null, 'title_asc');
        $this->assertSame(2, intval($results['total']));
        $this->assertSame('B1', $results['bookmarks'][0]['bTitle']);
        $this->assertSame('B2', $results['bookmarks'][1]['bTitle']);

        $results = $bs->getBookmarks(0, NULL, 1, 'ee', null, 'title_asc');
        $this->assertSame(2, intval($results['total']));
        $this->assertSame('B2', $results['bookmarks'][0]['bTitle']);
        $this->assertSame('B3', $results['bookmarks'][1]['bTitle']);

        $results = $bs->getBookmarks(0, NULL, 1, 'ii', null, 'title_asc');
        $this->assertSame(2, intval($results['total']));
        $this->assertSame('B2', $results['bookmarks'][0]['bTitle']);
        $this->assertSame('B3', $results['bookmarks'][1]['bTitle']);

        $results = $bs->getBookmarks(0, NULL, 1, 'aa+ee');

        $this->assertSame(1, intval($results['total']));
        $this->assertSame('B2', $results['bookmarks'][0]['bTitle']);

    }

    public function testStatsBetweenTags()
    {
        $tsts = $this->tsts;
        $tts = $this->tts;

        // basic functions
        $this->assertFalse($tsts->existStat('a', '>', 10));
        $tsts->setNbDescendants('a', '>', 10, 2);
        $this->assertSame(2, $tsts->getNbDescendants('a', '>', 10));
        $tsts->setMaxDepth('a', '>', 10, 3);
        $this->assertSame(3, $tsts->getMaxDepth('a', '>', 10));
        $this->assertTrue($tsts->existStat('a', '>', 10));
        $this->assertFalse($tsts->existStat('a', '>', 20));
        $tsts->increaseNbUpdate('a', '>', 10);
        $this->assertSame(1, $tsts->getNbUpdates('a', '>', 10));

        $tsts->deleteAll();

        // no structure
        $nbC = $tsts->getNbChildren('a', '>', 1);
        $nbD = $tsts->getNbDescendants('a', '>', 1);
        $maxDepth = $tsts->getMaxDepth('a', '>', 1);
        $this->assertSame(0, $nbC);
        $this->assertSame(0, $nbD);
        $this->assertSame(0, $maxDepth);

        // simple case
        $tts->addLinkedTags('b', 'c', '>', 1);
        $tts->addLinkedTags('a', 'd', '>', 1);
        $tts->addLinkedTags('a', 'b', '>', 1);
        $tts->addLinkedTags('b', 'e', '>', 1);

        $this->assertSame(3, $tsts->getNbUpdates('a', '>', '1'));
        $this->assertSame(2, $tsts->getNbUpdates('b', '>', '1'));
        $this->assertSame(0, $tsts->getNbUpdates('c', '>', '1'));
        $this->assertSame(0, $tsts->getNbUpdates('d', '>', '1'));
        $this->assertSame(0, $tsts->getNbUpdates('e', '>', '1'));


        $nbC = $tsts->getNbChildren('a', '>', 1);
        $nbD = $tsts->getNbDescendants('a', '>', 1);
        $maxDepth = $tsts->getMaxDepth('a', '>', 1);
        $this->assertSame(2, $nbC);
        $this->assertSame(4, $nbD);
        $this->assertSame(2, $maxDepth);

        $nbC = $tsts->getNbChildren('b', '>', 1);
        $nbD = $tsts->getNbDescendants('b', '>', 1);
        $maxDepth = $tsts->getMaxDepth('b', '>', 1);
        $this->assertSame(2, $nbC);
        $this->assertSame(2, $nbD);
        $this->assertSame(1, $maxDepth);

        $nbC = $tsts->getNbChildren('c', '>', 1);
        $nbD = $tsts->getNbDescendants('c', '>', 1);
        $maxDepth = $tsts->getMaxDepth('c', '>', 1);
        $this->assertSame(0, $nbC);
        $this->assertSame(0, $nbD);
        $this->assertSame(0, $maxDepth);

        $nbC = $tsts->getNbChildren('d', '>', 1);
        $nbD = $tsts->getNbDescendants('d', '>', 1);
        $maxDepth = $tsts->getMaxDepth('d', '>', 1);
        $this->assertSame(0, $nbC);
        $this->assertSame(0, $nbD);
        $this->assertSame(0, $maxDepth);

        // deletion
        $tts->removeLinkedTags('b', 'e', '>', 1);

        $nbC = $tsts->getNbChildren('b', '>', 1);
        $nbD = $tsts->getNbDescendants('b', '>', 1);
        $maxDepth = $tsts->getMaxDepth('b', '>', 1);
        $this->assertSame(1, $nbC);
        $this->assertSame(1, $nbD);
        $this->assertSame(1, $maxDepth);

        $nbC = $tsts->getNbChildren('a', '>', 1);
        $nbD = $tsts->getNbDescendants('a', '>', 1);
        $maxDepth = $tsts->getMaxDepth('a', '>', 1);
        $this->assertSame(2, $nbC);
        $this->assertSame(3, $nbD);
        $this->assertSame(2, $maxDepth);

        //do cases for synonyms

        $this->markTestSkipped('Check stats');

        $tsts->deleteAll();
        $tts->deleteAll();

        $tts->addLinkedTags('a', 'b', '>', 1);
        $tts->addLinkedTags('b', 'c', '=', 1);
        /*$tts->addLinkedTags('a', 'c', '>', 1);
         $tts->addLinkedTags('j', 'i', '=', 1);
         $tts->addLinkedTags('f', 'i', '=', 1);
         $tts->addLinkedTags('d', 'f', '>', 1);
         $tts->addLinkedTags('d', 'e', '>', 1);
         $tts->addLinkedTags('j', 'k', '>', 1);*/

        $nbC = $tsts->getNbChildren('a', '>', 1);
        $nbD = $tsts->getNbDescendants('a', '>', 1);
        $nbU = $tsts->getNbUpdates('a', '>', 1);
        $maxDepth = $tsts->getMaxDepth('a', '>', 1);
        //$this->assertSame(2, $tts->getLinkedTags('a', '>', 1));
        $this->assertSame(1, $nbC);
        //$this->assertSame(2, $nbD);
        $this->assertSame(2, $nbU);
        $this->assertSame(1, $maxDepth);

        // advanced case with fore loop
        //$tts->addLinkedTags('d', 'c', '>', 1);

        // advanced case with back loop
        //$tts->addLinkedTags('e', 'a', '>', 1);
    }

    public function testRenameFunction()
    {
        $tts = $this->tts;
        $b2ts = $this->b2ts;
        $bs = $this->bs;
        $tsts = $this->tsts;

        $uid1 = $this->addUser();
        $uid2 = $this->addUser();

        // with classic tags (users 10 & 20)
        $bid1 = $bs->addBookmark(
            "http://site1.com", "title", "description", 'note', 0,
            array('tag1', 'tag11', 'tag111'), null, null, false, false,
            $uid1
        );
        $bid2 = $bs->addBookmark(
            "http://site1.com", "title2", "description2", 'note', 0,
            array('tag2', 'tag22', 'tag222'), null, null, false, false,
            $uid2
        );

        $bookmarks = $bs->getBookmarks();
        $this->assertEquals(1, $bookmarks['total']);

        $b2ts->renameTag($uid1, 'tag1', 'newtag1');
        $tags1 = $b2ts->getTagsForBookmark($bid1);
        $this->assertContains('newtag1', $tags1);
        $this->assertContains('tag11', $tags1);
        $this->assertContains('tag111', $tags1);

        //should not be changed
        $tags2 = $b2ts->getTagsForBookmark($bid2);
        $this->assertContains('tag2', $tags2);
        $this->assertContains('tag22', $tags2);
        $this->assertContains('tag222', $tags2);


        // with linked tags

        $tts->addLinkedTags('b', 'c', '>', 1);
        $tts->addLinkedTags('a', 'b', '>', 1);
        $tts->addLinkedTags('b', 'a', '>', 2); // should not be modified because of userid

        $tts->renameTag(1, 'b', 'e');
        $linkedTags = $tts->getLinkedTags('e', '>', 1);
        $this->assertSame(array('c'), $linkedTags);
        $linkedTags = $tts->getLinkedTags('a', '>', 1);
        $this->assertSame(array('e'), $linkedTags);
        $linkedTags = $tts->getLinkedTags('b', '>', 2);
        $this->assertSame(array('a'), $linkedTags);

        //with stats

    }

    // Cannot be test because the function use GLOBALS variables
    // not taken into account by tests
    /*public function testMenuTags()
     {
     $tts = $this->tts;
     $bs = $this->bs;

     $bs->addBookmark("http://site1.com", "title", "description", "status", array('menu>tag1'), null, false, false, 1);
     $bs->addBookmark("http://site1.com", "title2", "description2", "status", array('menu>tag2>tag3'), null, false, false, 1);
     $bs->addBookmark("http://site1.com", "title3", "description3", "status", array('menu>tag1', 'menu>tag4'), null, false, false, 2);

     $menuTags = $tts->getMenuTags($uId);
     $this->assertEquals(3, sizeof($menuTags));
     $this->assertContains('tag1', $menuTags);
     $this->assertContains('tag2', $menuTags);
     $this->assertContains('tag4', $menuTags);

     }*/
}
?>
