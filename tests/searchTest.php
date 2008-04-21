<?php
require_once 'PHPUnit/Framework.php';

/*
To launch this test, type the following line into a shell
at the root of the scuttlePlus directory :
     phpunit SearchTest tests/searchTest.php
*/

class SearchTest extends PHPUnit_Framework_TestCase
{
    protected $us;
    protected $bs;
    protected $b2ts;
    protected $tts;
    protected $shs;
 
    protected function setUp()
    {
        global $dbhost, $dbuser, $dbpass, $dbname, $dbport, $dbpersist, $dbtype, $tableprefix;
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
	$this->shs =& ServiceFactory::getServiceInstance('SearchHistoryService');
	$this->shs->deleteAll();
    }

    public function testSearchHistory()
    {
	$shs = $this->shs;

	$terms = 'bbqsdkbb;,:,:q;,qddds&é"\'\\\\\(-è_çà)';
	$terms2 = '~#{|`]';
	$range = 'all';
	$nbResults = 10908;
	$uId = 10;

	$shs->addSearch($terms, $range, $nbResults, $uId);
	$shs->addSearch($terms2, $range, $nbResults, $uId);
	$shs->addSearch('', $range, $nbResults, $uId);    // A void search must not be saved

	$searches = $shs->getAllSearches();
	$this->assertSame(2, count($searches));
	$searches = $shs->getAllSearches($range, $uId);
	$this->assertEquals(2, count($searches));
	$searches = $shs->getAllSearches($range, 20);  // fake userid
	$this->assertEquals(0, count($searches));
	$searches = $shs->getAllSearches($range, $uId, 1);
	$this->assertEquals(1, count($searches));
	$searches = $shs->getAllSearches($range, null, 1, 1);
	$this->assertEquals(1, count($searches));

	//test content of results
	$searches = $shs->getAllSearches();
	$this->assertSame($terms2, $searches[0]['shTerms']);
	$this->assertSame($range, $searches[0]['shRange']);
	$this->assertEquals($nbResults, $searches[0]['shNbResults']);
	$this->assertEquals($uId, $searches[0]['uId']);
	$this->assertSame($terms, $searches[1]['shTerms']);
	$this->assertSame($range, $searches[1]['shRange']);
	$this->assertEquals($nbResults, $searches[1]['shNbResults']);
	$this->assertEquals($uId, $searches[1]['uId']);

	//test distinct parameter
	$shs->addSearch($terms,  $range, $nbResults, 30); // we repeat a search (same terms)
	$searches = $shs->getAllSearches();
	$this->assertSame(3, count($searches));
	$searches = $shs->getAllSearches(NULL, NULL, NULL, NULL, true);
	$this->assertSame(2, count($searches));
    }
}
?>
