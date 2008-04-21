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
	require_once('./header.inc.php');

	$this->ts =& ServiceFactory::getServiceInstance('TagService');
	$this->ts->deleteAll();
	$this->us =& ServiceFactory::getServiceInstance('UserService');
	$this->bs =& ServiceFactory::getServiceInstance('BookmarkService');
	$this->bs->deleteAll();
	$this->b2ts =& ServiceFactory::getServiceInstance('Bookmark2TagService');
	$this->b2ts->deleteAll();
	$this->tts =& ServiceFactory::getServiceInstance('Tag2TagService');
	$this->tts->deleteAll(); 
	$this->tsts =& ServiceFactory::getServiceInstance('TagStatService');
	$this->tsts->deleteAll();
    }
 
    public function testTagDescriptions()
    {
	$ts = $this->ts;

	$desc = $ts->getAllDescriptions('tag1');
	$this->assertSame(array(), $desc);

	$desc = $ts->getDescription('tag1', 1); // user 1
	$this->assertSame(array(), $desc);	

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
	$this->assertSame(array(), $desc);
	$desc = $ts->getDescription('tag2', 10);
	$this->assertEquals(array('tag'=>'tag2', 'uId'=>10, 'tDescription'=>'xxx'), $desc);	
	
    }

}
?>
