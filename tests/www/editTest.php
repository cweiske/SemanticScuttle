<?php
require_once dirname(__FILE__) . '/../prepare.php';
require_once 'HTTP/Request2.php';

class www_editTest extends TestBaseApi
{
    protected $urlPart = 'api/posts/add';

    /**
     * Test that the default privacy setting is used when an existing
     * bookmark is updated with edit.php.
     */
    public function testDefaultPrivacyEdit()
    {
        $this->setUnittestConfig(
            array('defaults' => array('privacy' => 2))
        );

        list($req, $uId) = $this->getLoggedInRequest('?unittestMode=1');
        $cookies = $req->getCookieJar();
        $req->setMethod(HTTP_Request2::METHOD_POST);
        $req->addPostParameter('url', 'http://www.example.org/testdefaultprivacyposts_edit');
        $req->addPostParameter('description', 'Test bookmark 2 for default privacy.');
        $req->addPostParameter('status', '0');
        $res = $req->send();
        $this->assertEquals(
            200, $res->getStatus(),
            'Adding bookmark failed: ' . $res->getBody());
        $bms = $this->bs->getBookmarks(0, null, $uId);
        $bm  = reset($bms['bookmarks']);
        $bmId = $bm['bId'];

        $reqUrl = $GLOBALS['unittestUrl'] . 'edit.php/' . $bmId . '?unittestMode=1';
        $req2 = new HTTP_Request2($reqUrl, HTTP_Request2::METHOD_POST);
        $req2->setCookieJar($cookies);
        $req2->addPostParameter('address', 'http://www.example.org/testdefaultprivacyposts_edit');
        $req2->addPostParameter('title', 'Test bookmark 2 for default privacy.');
        $req2->addPostParameter('submitted', '1');
        $res = $req2->send();

        $this->assertEquals(302, $res->getStatus(), 'Editing bookmark failed');

        $bm = $this->bs->getBookmark($bmId);
        $this->assertEquals('2', $bm['bStatus']);
    }//end testDefaultPrivacyEdit

}//end class www_editTest
?>
