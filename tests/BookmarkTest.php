<?php
/**
 * SemanticScuttle - your social bookmark manager.
 *
 * PHP version 5.
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Christian Weiske <cweiske@cweiske.de>
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
 * @author Christian Weiske <cweiske@cweiske.de>
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
		$this->us =SemanticScuttle_Service_Factory::get('User');
		$this->bs =SemanticScuttle_Service_Factory::get('Bookmark');
		$this->bs->deleteAll();
		$this->b2ts=SemanticScuttle_Service_Factory::get('Bookmark2Tag');
		$this->b2ts->deleteAll();
		$this->tts =SemanticScuttle_Service_Factory::get('Tag2Tag');
		$this->tts->deleteAll();
		$this->tsts =SemanticScuttle_Service_Factory::get('TagStat');
		$this->tsts->deleteAll();
	}

	public function testHardCharactersInBookmarks()
	{		
		$bs = $this->bs;
		$title = "title&é\"'(-è_çà)=";
		$desc = "description#{[|`\^@]}³<> ¹¡÷×¿&é\"'(-è\\_çà)=";
		$tag1 = "#{|`^@]³¹¡¿<&é\"'(-è\\_çà)";	
		$tag2 = "&é\"'(-è.[?./§!_çà)";

		$bs->addBookmark(
            'http://site1.com', $title, $desc, 'note',
            0, array($tag1, $tag2),
            null, false, false, 1
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

		$bs->addBookmark(
            'http://site1.com', "title", "description", 'note',
            0, array('tag1'), null, false, false,
            1
        );
		$bs->addBookmark(
            "http://site1.com", "title2", "description2", 'note',
            0, array('tag2'), null, false, false,
            2
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



    public function testDeleteBookmark()
    {
        //FIXME
    }

}


if (PHPUnit_MAIN_METHOD == 'BookmarkTest::main') {
    BookmarkTest::main();
}
?>
