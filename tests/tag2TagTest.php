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
    }
 
    public function testManipulateTag2TagRelations()
    {
	$tts = $this->tts;

	$tts->addLinkedTags('a', 'b', '>', 1);
	$tts->addLinkedTags('a', 'c', '>', 1);
	$tts->addLinkedTags('a', 'd', '>', 20);
	$tts->addLinkedTags('b', 'a', '>', 1); //warning: recursive link
 	$tts->addLinkedTags('b', 'd', '>', 1);
 	$tts->addLinkedTags('d', 'e', '>', 1);
	$tts->addLinkedTags('d', 'e', '>', 20);
	$tts->addLinkedTags('f', 'g', '>', 20);

	$allLinkedTags = $tts->getAllLinkedTags('a', '>', 1, false); //as tree
	$this->assertSame(array('node'=>'a', array('node'=>'b', 'a', array('node'=>'d', 'e')), 'c'), $allLinkedTags);
	$allLinkedTags = $tts->getAllLinkedTags('a', '>', 1, true); // as flat list
	$this->assertEquals(5, sizeof($allLinkedTags));
	$this->assertTrue(in_array('a', $allLinkedTags));
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
	
	$linkedTags = $tts->getLinkedTags('a', '>', 1);
	$this->assertSame(array('b', 'c'), $linkedTags);
	$tts->removeLinkedTags('a', 'b', '>', 1);
	$linkedTags = $tts->getLinkedTags('a', '>', 1);
	$this->assertSame(array('c'), $linkedTags);
	$tts->removeLinkedTags('a', 'c', '>', 1);
	$linkedTags = $tts->getLinkedTags('a', '>', 1);
	$this->assertEquals(0, sizeof($linkedTags));
    }

   public function testAddLinkedTagsThroughBookmarking()
    {
	$bs = $this->bs;
	$tags = array('a>b', 'b>c', 'a>d>e', 'a>a', 'a');
	$bs->addBookmark("http://google.com", "title", "description", "status", $tags, null, false, false, 1);
	$bookmark = $bs->getBookmarkByAddress("http://google.com");

	$ts = $this->ts;
	$savedTags = $ts->getTagsForBookmark(intval($bookmark['bId']));
	$this->assertEquals(4, sizeof($savedTags));
	$this->assertContains('b', $savedTags);
	$this->assertContains('c', $savedTags);
	$this->assertContains('e', $savedTags);
	$this->assertContains('a', $savedTags);

	$tts = $this->tts;
	$linkedTags = $tts->getLinkedTags('a', '>', 1);
	$this->assertEquals(2, sizeof($linkedTags));
	$this->assertSame('b', $linkedTags[0]['tag']);
	$this->assertSame('d', $linkedTags[1]['tag']);
	$linkedTags = $tts->getLinkedTags('b', '>', 1);
	$this->assertEquals(1, sizeof($linkedTags));
	$this->assertSame('c', $linkedTags[0]['tag']);
	$this->assertTrue($tts->existsLinkedTags('d', 'e', '>', 1));
    }

    public function testSearchThroughLinkedTags()
    {
	$tts = $this->tts;
	$bs = $this->bs;

	$tags1 = array('aa>bb>cc', 'dd');
	$bs->addBookmark("web.com", "B1", "description", "status", $tags1, null, false, false, 1);
	$tags = array('bb>gg', 'ee>ff');
	$bs->addBookmark("web.com", "B2", "description", "status", $tags, null, false, false, 1);
	$tags = array('ee');
	$bs->addBookmark("web.com", "B3", "description", "status", $tags, null, false, false, 1);

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
	$this->assertSame('B2', $results['bookmarks'][0]['bTitle']);
	$this->assertSame('B3', $results['bookmarks'][1]['bTitle']);

	$results = $bs->getBookmarks(0, NULL, 1, 'aa+ee');
	$this->assertSame(1, intval($results['total']));
	$this->assertSame('B2', $results['bookmarks'][0]['bTitle']);

    }

}
?>
