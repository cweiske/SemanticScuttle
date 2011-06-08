<?php
require_once dirname(__FILE__) . '/../prepare.php';
require_once 'HTTP/Request2.php';

class www_bookmarksTest extends TestBaseApi
{
    protected $urlPart = 'api/posts/add';

    /**
     * Test that the default privacy setting is selected in the Privacy
     * drop-down list when adding a new bookmark, sending the form and
     * missing the title and the privacy setting.
     */
    public function testDefaultPrivacyBookmarksAddMissingTitleMissingPrivacy()
    {
        $this->setUnittestConfig(
            array('defaults' => array('privacy' => 2))
        );
        list($req, $uId) = $this->getLoggedInRequest();
        $cookies = $req->getCookieJar();
        $req->setMethod(HTTP_Request2::METHOD_POST);
        $req->addPostParameter('url', 'http://www.example.org/testdefaultprivacyposts_bookmarksget');
        $req->addPostParameter('description', 'Test bookmark 1 for default privacy.');
        $req->addPostParameter('status', '0');
        $req->send();

        $bms = $this->bs->getBookmarks(0, null, $uId);
        $this->assertEquals(1, count($bms['bookmarks']));
        $user = $this->us->getUser($uId);
        $reqUrl = $GLOBALS['unittestUrl'] . 'bookmarks.php/' . $user['username'] . '?action=get' . '&unittestMode=1';

        list($req, $uId) = $this->getAuthRequest('?unittestMode=1');
        $req->setMethod(HTTP_Request2::METHOD_POST);
        $req->setUrl($reqUrl);
        $req->setCookieJar($cookies);
        $req->addPostParameter('submitted', '1');
        $response = $req->send();
        $response_body = $response->getBody();

        $x = simplexml_load_string($response_body);
        $ns = $x->getDocNamespaces();
        $x->registerXPathNamespace('ns', reset($ns));

        $elements = $x->xpath('//ns:select[@name="status"]/ns:option[@selected="selected"]');
        $this->assertEquals(1, count($elements), 'No selected status option found');
        $this->assertEquals(2, (string)$elements[0]['value']);
    }//end testDefaultPrivacyBookmarksAddMissingTitleMissingPrivacy


    /**
     * Test that the default privacy setting is selected in the Privacy
     * drop-down list when a new bookmark is being created.
     */
    public function testDefaultPrivacyBookmarksAdd()
    {
        $this->setUnittestConfig(
            array('defaults' => array('privacy' => 1))
        );
        list($req, $uId) = $this->getLoggedInRequest('?unittestMode=1');

        $user = $this->us->getUser($uId);
        $reqUrl = $GLOBALS['unittestUrl'] . 'bookmarks.php/'
            . $user['username'] . '?action=add' . '&unittestMode=1';
        $req->setUrl($reqUrl);
        $req->setMethod(HTTP_Request2::METHOD_GET);
        $response = $req->send();
        $response_body = $response->getBody();
        $this->assertNotEquals('', $response_body, 'Response is empty');

        $x = simplexml_load_string($response_body);
        $ns = $x->getDocNamespaces();
        $x->registerXPathNamespace('ns', reset($ns));

        $elements = $x->xpath('//ns:select[@name="status"]/ns:option[@selected="selected"]');
        $this->assertEquals(1, count($elements), 'No selected status option found');
        $this->assertEquals(1, (string)$elements[0]['value']);
    }//end testDefaultPrivacyBookmarksAdd


    /**
     * Test that the private RSS link exists when a user
     * has a private key and is enabled
     */
    public function testVerifyPrivateRSSLinkExists()
    {
        list($req, $uId) = $this->getLoggedInRequest('?unittestMode=1', true, true);

        $user = $this->us->getUser($uId);
        $reqUrl = $GLOBALS['unittestUrl'] . 'bookmarks.php/'
            . $user['username'] . '?unittestMode=1';
        $req->setUrl($reqUrl);
        $req->setMethod(HTTP_Request2::METHOD_GET);
        $response = $req->send();
        $response_body = $response->getBody();
        $this->assertNotEquals('', $response_body, 'Response is empty');

        $x = simplexml_load_string($response_body);
        $ns = $x->getDocNamespaces();
        $x->registerXPathNamespace('ns', reset($ns));

        $elements = $x->xpath('//ns:link[@rel="alternate" and @type="application/rss+xml"]');
        $this->assertEquals(2, count($elements), 'Number of Links in Head not correct');
        $this->assertContains('privatekey=', (string)$elements[1]['href']);
    }//end testVerifyPrivateRSSLinkExists


    /**
     * Test that the private RSS link doesn't exists when a user
     * does not have a private key or is not enabled
     */
    public function testVerifyPrivateRSSLinkDoesNotExist()
    {
        list($req, $uId) = $this->getLoggedInRequest('?unittestMode=1', true);

        $user = $this->us->getUser($uId);
        $reqUrl = $GLOBALS['unittestUrl'] . 'bookmarks.php/'
            . $user['username'] . '?unittestMode=1';
        $req->setUrl($reqUrl);
        $req->setMethod(HTTP_Request2::METHOD_GET);
        $response = $req->send();
        $response_body = $response->getBody();
        $this->assertNotEquals('', $response_body, 'Response is empty');

        $x = simplexml_load_string($response_body);
        $ns = $x->getDocNamespaces();
        $x->registerXPathNamespace('ns', reset($ns));

        $elements = $x->xpath('//ns:link[@rel="alternate" and @type="application/rss+xml"]');
        $this->assertEquals(1, count($elements), 'Number of Links in Head not correct');
        $this->assertNotContains('privatekey=', (string)$elements[0]['href']);
    }//end testVerifyPrivateRSSLinkDoesNotExist

}//end class www_bookmarksTest
?>
