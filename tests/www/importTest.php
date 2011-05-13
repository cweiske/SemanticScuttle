<?php
require_once dirname(__FILE__) . '/../prepare.php';
require_once 'HTTP/Request2.php';

class www_importTest extends TestBaseApi
{
    protected $urlPart = 'import.php';

    /**
     * Test that the default privacy setting is used when bookmarks
     * are imported from an XML bookmarks file using import.php.
     */
    public function testDefaultPrivacyImport()
    {
        $this->setUnittestConfig(
            array('defaults' => array('privacy' => 2))
        );
        list($req, $uId) = $this->getLoggedInRequest();
        $req->setMethod(HTTP_Request2::METHOD_POST);
        $req->setUrl($GLOBALS['unittestUrl'] . 'import.php' . '?unittestMode=1');
        $req->addUpload('userfile', dirname(__FILE__) . '/../data/BookmarkTest_deliciousbookmarks.xml');
        $res = $req->send();
        $this->assertEquals(302, $res->getStatus(), 'Bookmark import failed');

        $this->us->setCurrentUserId($uId);
        $bms = $this->bs->getBookmarks(0, null, $uId);
        $this->assertEquals(3, count($bms['bookmarks']));
        $bm = reset($bms['bookmarks']);
        $this->assertEquals('2', $bm['bStatus']);
    }//end testDefaultPrivacyImport

}//end class www_importTest
?>
