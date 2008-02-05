<?php
require_once 'PHPUnit/Framework.php';

/*
To launch this test, type the following line into a shell
at the root of the scuttlePlus directory :
     phpunit Tag2TagTest tests/tag2TagTest.php
*/

class Tag2TagTest extends PHPUnit_Framework_TestCase
{
    protected $us;
    protected $bs;
    protected $ts;
    protected $tts;
 
    protected function setUp()
    {
        global $dbhost, $dbuser, $dbpass, $dbname, $dbport, $dbpersist, $dbtype, $tableprefix;
	require_once('./header.inc.php');

	$this->us =& ServiceFactory::getServiceInstance('UserService');
	$this->bs =& ServiceFactory::getServiceInstance('BookmarkService');
	$this->bs->deleteAll();
	$this->ts =& ServiceFactory::getServiceInstance('TagService');
	$this->ts->deleteAll();
	$this->tts =& ServiceFactory::getServiceInstance('Tag2TagService');
	$this->tts->deleteAll(); 
	$this->tsts =& ServiceFactory::getServiceInstance('TagStatService');
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

	$allLinkedTags = $tts->getAllLinkedTags('e', '>', 1, true); // as flat list
	$this->assertEquals(array(), $allLinkedTags);

	$allLinkedTags = $tts->getAllLinkedTags('d', '>', 1, true); // as flat list
	$this->assertEquals(array('e'), $allLinkedTags);

	$allLinkedTags = $tts->getAllLinkedTags('b', '>', 1, true); // as flat list
	$this->assertEquals(array('d', 'e'), $allLinkedTags);
	$this->assertEquals(2, sizeof($allLinkedTags));
	$this->assertTrue(in_array('d', $allLinkedTags));
	$this->assertTrue(in_array('e', $allLinkedTags));

	$allLinkedTags = $tts->getAllLinkedTags('a', '>', 1, true); // as flat list
	$this->assertEquals(4, sizeof($allLinkedTags));
	$this->assertTrue(in_array('b', $allLinkedTags));
	$this->assertTrue(in_array('c', $allLinkedTags));
	$this->assertTrue(in_array('d', $allLinkedTags));
	$this->assertTrue(in_array('e', $allLinkedTags));


	// warning: we add recursive link
	$tts->addLinkedTags('b', 'a', '>', 1); 

	$allLinkedTags = $tts->getAllLinkedTags('a', '>', 1, true); // as flat list
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
	$allLinkedTags = $tts->getAllLinkedTags('a', '=', 1, true); // as flat list
	$this->assertEquals(0, sizeof($allLinkedTags));

	$allLinkedTags = $tts->getAllLinkedTags('b', '=', 1, true); // as flat list
	$this->assertEquals(3, sizeof($allLinkedTags));
	$this->assertTrue(in_array('d', $allLinkedTags));
	$this->assertTrue(in_array('e', $allLinkedTags));
	$this->assertTrue(in_array('f', $allLinkedTags));

	$allLinkedTags = $tts->getAllLinkedTags('f', '>', 1, true); // as flat list
	$this->assertEquals(5, sizeof($allLinkedTags));
	$this->assertTrue(in_array('b', $allLinkedTags));
	$this->assertTrue(in_array('d', $allLinkedTags));
	$this->assertTrue(in_array('e', $allLinkedTags));
	$this->assertTrue(in_array('c', $allLinkedTags));
	$this->assertTrue(in_array('g', $allLinkedTags));

	$allLinkedTags = $tts->getAllLinkedTags('a', '>', 1, true); // as flat list
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

	$allLinkedTags = $tts->getAllLinkedTags('j', '>', 1, true); // as flat list
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
	$allLinkedTags = $tts->getAllLinkedTags('b', '>', 1, true); // as flat list
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
	$bs = $this->bs;
	$tags = array('a>b', 'b>c', 'a>d>e', 'a>a', 'a', 'r=s', 's=t=u');
	$bs->addBookmark("http://google.com", "title", "description", "status", $tags, null, false, false, 1);
	$bookmark = $bs->getBookmarkByAddress("http://google.com");

	$ts = $this->ts;
	$savedTags = $ts->getTagsForBookmark(intval($bookmark['bId']));
	$this->assertEquals(6, sizeof($savedTags));
	$this->assertContains('b', $savedTags);
	$this->assertContains('c', $savedTags);
	$this->assertContains('e', $savedTags);
	$this->assertContains('a', $savedTags);
	$this->assertContains('r', $savedTags);
	$this->assertContains('s', $savedTags);

	$tts = $this->tts;
	$linkedTags = $tts->getLinkedTags('a', '>', 1);
	$this->assertEquals(2, sizeof($linkedTags));
	$this->assertSame('b', $linkedTags[0]['tag']);
	$this->assertSame('d', $linkedTags[1]['tag']);
	$linkedTags = $tts->getLinkedTags('b', '>', 1);
	$this->assertEquals(1, sizeof($linkedTags));
	$this->assertSame('c', $linkedTags[0]['tag']);
	$this->assertTrue($tts->existsLinkedTags('d', 'e', '>', 1));
	$this->assertFalse($tts->existsLinkedTags('e', 'd', '>', 1));
    }

    public function testSearchThroughLinkedTags()
    {
	$tts = $this->tts;
	$bs = $this->bs;

	$tags = array('aa>bb>cc', 'dd');
	$bs->addBookmark("web1.com", "B1", "description", "status", $tags, null, false, false, 1);
	$tags = array('bb>gg', 'ee>ff');
	$bs->addBookmark("web2.com", "B2", "description", "status", $tags, null, false, false, 1);
	$tags = array('ee=ii');
	$bs->addBookmark("web3.com", "B3", "description", "status", $tags, null, false, false, 1);

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
	$results = $bs->getBookmarks(0, NULL, 1, 'aa');
	$this->assertSame(2, intval($results['total']));
	$this->assertSame('B1', $results['bookmarks'][0]['bTitle']);
	$this->assertSame('B2', $results['bookmarks'][1]['bTitle']);

	$results = $bs->getBookmarks(0, NULL, 1, 'ee');
	$this->assertSame(2, intval($results['total']));
	$this->assertSame('B2', $results['bookmarks'][1]['bTitle']);
	$this->assertSame('B3', $results['bookmarks'][0]['bTitle']);

	$results = $bs->getBookmarks(0, NULL, 1, 'ii');
	$this->assertSame(2, intval($results['total']));
	$this->assertSame('B2', $results['bookmarks'][1]['bTitle']);
	$this->assertSame('B3', $results['bookmarks'][0]['bTitle']);

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
	$this->assertSame(1, $tsts->getNbUpdate('a', '>', 10));

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

	$this->assertSame(3, $tsts->getNbUpdate('a', '>', '1'));
	$this->assertSame(2, $tsts->getNbUpdate('b', '>', '1'));
	$this->assertSame(0, $tsts->getNbUpdate('c', '>', '1'));
	$this->assertSame(0, $tsts->getNbUpdate('d', '>', '1'));
	$this->assertSame(0, $tsts->getNbUpdate('e', '>', '1'));


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


	// advanced case with fore loop
	//$tts->addLinkedTags('d', 'c', '>', 1);

	// advanced case with back loop
	//$tts->addLinkedTags('e', 'a', '>', 1);
    }
}
?>
