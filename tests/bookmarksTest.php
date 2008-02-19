<?php
require_once 'PHPUnit/Framework.php';

/*
To launch this test, type the following line into a shell
at the root of the scuttlePlus directory :
     phpunit BookmarksTest tests/bookmarksTest.php
*/

class BookmarksTest extends PHPUnit_Framework_TestCase
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
 
    public function testUnificationOfBookmarks()
    {
	$bs = $this->bs;

	$bs->addBookmark("http://site1.com", "title", "description", "status", array('tag1'), null, false, false, 1);
	$bs->addBookmark("http://site1.com", "title2", "description2", "status", array('tag2'), null, false, false, 2);

	$bookmarks =& $bs->getBookmarks(0, 1, NULL, NULL, NULL, getSortOrder(), NULL, 0, $dtend);
    }

    public function testSearchingBookmarksAccentsInsensible()
    {
	$bs = $this->bs;

	$bs->addBookmark("http://site1.com", "title", "éèüaàê", "status", array('tag1'), null, false, false, 1);
	$bookmarks =& $bs->getBookmarks(0, NULL, NULL, NULL, $terms = "eeaae"); //void
	$this->assertEquals(0, $bookmarks['total']);
	$bookmarks =& $bs->getBookmarks(0, NULL, NULL, NULL, $terms = "eeuaae");
	$this->assertEquals(1, $bookmarks['total']);
    }

}
?>
