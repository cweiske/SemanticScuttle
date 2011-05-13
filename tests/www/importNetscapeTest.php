<?php
require_once dirname(__FILE__) . '/../prepare.php';
require_once 'HTTP/Request2.php';

class www_importNetscapeTest extends TestBaseApi
{
    protected $urlPart = 'importNetscape.php';

    /**
     * Test that the default privacy setting is used when bookmarks
     * are imported from an HTML bookmarks file using importNetscape.php.
     */
    public function testDefaultPrivacyImportNetscape()
    {
        $this->setUnittestConfig(
            array('defaults' => array('privacy' => 1))
        );
        list($req, $uId) = $this->getLoggedInRequest();
        $req->setMethod(HTTP_Request2::METHOD_POST);
        $req->setUrl($GLOBALS['unittestUrl'] . 'importNetscape.php' . '?unittestMode=1');
        $req->addUpload('userfile', dirname(__FILE__) . '/../data/BookmarkTest_netscapebookmarks.html');
        $res = $req->send();
        $this->assertEquals(200, $res->getStatus(), 'Bookmark import failed');

        $this->us->setCurrentUserId($uId);
        $bms = $this->bs->getBookmarks(0, null, $uId);
        $this->assertEquals(3, count($bms['bookmarks']));
        $bm = reset($bms['bookmarks']);
        $this->assertEquals('1', $bm['bStatus']);
    }//end testDefaultPrivacyImportNetscape

}//end class www_importNetscapeTest
?>
