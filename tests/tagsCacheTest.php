<?php
require_once 'PHPUnit/Framework.php';

/*
 To launch this test, type the following line into a shell
 at the root of the scuttlePlus directory :
 phpunit TagsCacheTest tests/tagsCacheTest.php
 */

class TagsCacheTest extends PHPUnit_Framework_TestCase
{
	protected $us;
	protected $bs;
	protected $b2ts;
	protected $tts;

	protected function setUp()
	{
		global $dbhost, $dbuser, $dbpass, $dbname, $dbport, $dbpersist, $dbtype, $tableprefix, $TEMPLATES_DIR, $debugMode;
		require_once('./header.inc.php');

		$this->us =& ServiceFactory::getServiceInstance('UserService');
		$this->bs =& ServiceFactory::getServiceInstance('BookmarkService');
		$this->bs->deleteAll();
		$this->b2ts =& ServiceFactory::getServiceInstance('Bookmark2TagService');
		$this->b2ts->deleteAll();
		$this->tts =& ServiceFactory::getServiceInstance('Tag2TagService');
		$this->tts->deleteAll();
		$this->tsts =& ServiceFactory::getServiceInstance('TagStatService');
		$this->tsts->deleteAll();
		$this->tcs =& ServiceFactory::getServiceInstance('TagCacheService');
		$this->tcs->deleteAll();
	}

	public function testInclusionAllowsToAddAndDeleteChildrenTags() {
		//message_die(GENERAL_ERROR, $GLOBALS['dbname'].'1');

		$tts = $this->tts;
		$tcs = $this->tcs;

		// test adding children
		$tcs->addChild('a', 'b', 1);
		$tcs->addChild('a', 'c', 1);
		$this->assertEquals(array('b','c'), $tcs->getChildren('a', 1));

		// test adding a same child
		$tcs->addChild('a', 'b', 1);
		$this->assertEquals(array('b','c'), $tcs->getChildren('a', 1));

		// test removing a child
		$tcs->removeChild('a', 'b', 1);
		$this->assertEquals(array('c'), $tcs->getChildren('a', 1));

		// test removing a same child
		$tcs->removeChild('a', 'b', 1);
		$this->assertEquals(array('c'), $tcs->getChildren('a', 1));

		// test existing child
		$this->assertTrue($tcs->existsChild('a', 'c', 1));
		$this->assertTrue(!$tcs->existsChild('a', 'c', 2)); // wrong user
		$this->assertTrue(!$tcs->existsChild('a', 'b', 1)); // wrong child

		// test removing several children
		$tcs->addChild('e', 'f', 1);
		$tcs->addChild('e', 'g', 1);
		$tcs->addChild('e', 'h', 1);
		$tcs->removeChild('e', NULL, 1);

		$this->assertTrue(!$tcs->existsChild('e', 'f', 1));
		$this->assertTrue(!$tcs->existsChild('e', 'g', 1));
		$this->assertTrue(!$tcs->existsChild('e', 'h', 1));

	}

	public function testInclusionCacheIsUpdatedWhenATag2TagLinkIsCreatedOrRemoved() {
		$tts = $this->tts;
		$tcs = $this->tcs;

		// test inclusion without possible errors
		$tts->addLinkedTags('a', 'b', '>', 1);
		$tts->addLinkedTags('b', 'c', '>', 1);
		$tts->addLinkedTags('c', 'd', '>', 1);
		$tts->addLinkedTags('e', 'f', '>', 1);
		$tts->addLinkedTags('b', 'e', '>', 1);

		$this->assertSame(array('b','c','d','e','f'), $tts->getAllLinkedTags('a', '>', 1));
		$this->assertSame(array('c','d','e','f'), $tts->getAllLinkedTags('b', '>', 1));

		// test inclusion with deletion
		$tts->removeLinkedTags('b', 'c', '>', 1);
		$this->assertSame(array('b','e','f'), $tts->getAllLinkedTags('a', '>', 1));
		$this->assertSame(array('e','f'), $tts->getAllLinkedTags('b', '>', 1));
		$this->assertSame(array('d'), $tts->getAllLinkedTags('c', '>', 1));
		$this->assertSame(array('f'), $tts->getAllLinkedTags('e', '>', 1));

	}

	public function testInclusionResistsToTagCycles() {
		$tts = $this->tts;
		$tcs = $this->tcs;

		$tts->addLinkedTags('a', 'b', '>', 1);
		$tts->addLinkedTags('b', 'c', '>', 1);
		$tts->addLinkedTags('c', 'a', '>', 1); // creates cycle a>c>a
		
		$this->assertSame(array('b','c'), $tts->getAllLinkedTags('a', '>', 1));
		$this->assertSame(array('c', 'a'), $tts->getAllLinkedTags('b', '>', 1));
		$this->assertSame(array('a', 'b'), $tts->getAllLinkedTags('c', '>', 1));
	}

	public function testSynonymyAllowsToAddAndDeleteSynonyms() {
		$tts = $this->tts;
		$tcs = $this->tcs;

		// simple synonymy
		$tcs->addSynonym('a', 'b', 1);
		$tcs->addSynonym('a', 'c', 1);

		$this->assertEquals(array('b', 'c'), $tcs->getSynonyms('a', 1));
		$this->assertEquals(array('c', 'a'), $tcs->getSynonyms('b', 1));
		$this->assertEquals(array('b', 'a'), $tcs->getSynonyms('c', 1));

		//more advanced one 1
		$tcs->deleteByUser(1);
		$tcs->addSynonym('a', 'b', 1);
		$tcs->addSynonym('a', 'c', 1);
		$tcs->addSynonym('d', 'e', 1);
		$tcs->addSynonym('a', 'e', 1);
		$this->assertEquals(array('b', 'c', 'e', 'd'), $tcs->getSynonyms('a', 1));

		//more advanced one 2
		$tcs->deleteByUser(1);
		$tcs->addSynonym('a', 'b', 1);
		$tcs->addSynonym('a', 'c', 1);
		$tcs->addSynonym('d', 'e', 1);
		$tcs->addSynonym('a', 'd', 1);
		$this->assertEquals(array('b', 'c', 'd', 'e'), $tcs->getSynonyms('a', 1));

		//with Linked tags
		$tcs->deleteByUser(1);
		$tts->addLinkedTags('a', 'b', '=', 1);
		$tts->addLinkedTags('c', 'd', '=', 1);
		$tts->addLinkedTags('c', 'e', '=', 1);
		$tts->addLinkedTags('e', 'a', '=', 1);
		$this->assertEquals(array('b', 'e', 'c', 'd'), $tts->getAllLinkedTags('a', '=', 1));

	}

	public function testInclusionTakesSynonymsIntoAccount() {
		$tts = $this->tts;
		$tcs = $this->tcs;

		$tts->addLinkedTags('a', 'b', '>', 1);
		$tts->addLinkedTags('b', 'c', '>', 1);
		$tts->addLinkedTags('d', 'e', '>', 1);
		$tts->addLinkedTags('c', 'd', '=', 1);
		
		// results are put into cache
		$this->assertEquals(array('b', 'c', 'd', 'e'), $tts->getAllLinkedTags('a', '>', 1));
		$this->assertEquals(array('d', 'e'), $tts->getAllLinkedTags('c', '>', 1));

		// same results must be taken out from cache		
		$this->assertEquals(array('b', 'c', 'd', 'e'), $tts->getAllLinkedTags('a', '>', 1));
		$this->assertEquals(array('d', 'e'), $tts->getAllLinkedTags('c', '>', 1));
		
		//cache must be deleted for user when links are modified
		$tts->addLinkedTags('a', 'f', '=', 1);
		$this->assertEquals(array(), $tcs->getChildren('a', 1));
		$this->assertEquals(array(), $tcs->getSynonyms('d', 1));
	}
}
?>
