<?php
require_once 'PHPUnit/Framework.php';

/*
 To launch this test, type the following line into a shell
 at the root of the scuttlePlus directory :
 phpunit TagsTest tests/tagsTest.php
 */

class TagsTest extends PHPUnit_Framework_TestCase
{
	protected $ts;

	protected function setUp()
	{
		global $dbhost, $dbuser, $dbpass, $dbname, $dbport, $dbpersist, $dbtype, $tableprefix;
		require_once dirname(__FILE__) . '/../src/SemanticScuttle/header.php';

		$this->ts =SemanticScuttle_Service_Factory::get('Tag');
		$this->ts->deleteAll();
		$this->us =SemanticScuttle_Service_Factory::get('User');
		$this->bs =SemanticScuttle_Service_Factory::get('Bookmark');
		$this->bs->deleteAll();
		$this->b2ts =SemanticScuttle_Service_Factory::get('Bookmark2Tag');
		$this->b2ts->deleteAll();
		$this->tts =SemanticScuttle_Service_Factory::get('Tag2Tag');
		$this->tts->deleteAll();
		$this->tsts =SemanticScuttle_Service_Factory::get('TagStat');
		$this->tsts->deleteAll();
	}

	public function testTagDescriptions()
	{
		$ts = $this->ts;

		$desc = $ts->getAllDescriptions('tag1');
		$this->assertSame(array(), $desc);

		$desc = $ts->getDescription('tag1', 1); // user 1
		$this->assertSame(array('tDescription'=>''), $desc);

		$desc1 = "test description";
		$ts->updateDescription('tag1', 1, $desc1);  // first desc
		$desc = $ts->getDescription('tag1', 1);
		$this->assertEquals(array('tag'=>'tag1', 'uId'=>1, 'tDescription'=>$desc1), $desc);

		$desc1 = "&é\"'(-è_çà)=´~#'#{{[\\\\[||`\^\^@^@}¹²¡×¿ ?./§µ%";
		$ts->updateDescription('tag1', 1, $desc1); // update desc
		$desc = $ts->getDescription('tag1', 1);
		$this->assertEquals(array('tag'=>'tag1', 'uId'=>1, 'tDescription'=>$desc1), $desc);

		$desc2 = "æâ€êþÿûîîôôöŀï'üð’‘ßä«≤»©»  ↓¿×÷¡¹²³";
		$ts->updateDescription('tag1', 2, $desc2); // user 2
		$desc = $ts->getDescription('tag1', 2);
		$this->assertEquals(array('tag'=>'tag1', 'uId'=>2, 'tDescription'=>$desc2), $desc);

		$desc = $ts->getAllDescriptions('tag1');
		$this->assertEquals($desc, array(array('tag'=>'tag1', 'uId'=>1, 'tDescription'=>$desc1), array('tag'=>'tag1', 'uId'=>2, 'tDescription'=>$desc2)));

	}

	public function testRenameFunction()
	{
		$ts = $this->ts;

		$ts->updateDescription('tag1', 10, 'xxx');
		$ts->renameTag(10, 'tag1', 'tag2');
		$desc = $ts->getDescription('tag1', 10);
		$this->assertSame(array('tDescription'=>''), $desc);
		$desc = $ts->getDescription('tag2', 10);
		$this->assertEquals(array('tag'=>'tag2', 'uId'=>10, 'tDescription'=>'xxx'), $desc);

	}

}
?>
