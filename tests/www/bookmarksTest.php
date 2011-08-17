<?php
require_once dirname(__FILE__) . '/../prepare.php';
require_once 'HTTP/Request2.php';

class www_bookmarksTest extends TestBaseApi
{
    protected $urlPart = 'bookmarks.php';

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
        $user = $this->us->getUser($uId);
        $req->setMethod(HTTP_Request2::METHOD_POST);
        $req->setUrl($this->getTestUrl('/' . $user['username'] . '?action=get'));
        $req->addPostParameter('submitted', '1');
        $response = $req->send();
        $response_body = $response->getBody();

        $x = simplexml_load_string($response_body);
        $ns = $x->getDocNamespaces();
        $x->registerXPathNamespace('ns', reset($ns));

        $elements = $x->xpath(
            '//ns:select[@name="status"]/ns:option[@selected="selected"]'
        );
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
        list($req, $uId) = $this->getLoggedInRequest();

        $user = $this->us->getUser($uId);
        $req->setUrl($this->getTestUrl('/' . $user['username'] . '?action=add'));
        $response = $req->send();
        $response_body = $response->getBody();
        $this->assertNotEquals('', $response_body, 'Response is empty');

        $x = simplexml_load_string($response_body);
        $ns = $x->getDocNamespaces();
        $x->registerXPathNamespace('ns', reset($ns));

        $elements = $x->xpath(
            '//ns:select[@name="status"]/ns:option[@selected="selected"]'
        );
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
        $req->setUrl($this->getTestUrl('/' . $user['username']));
        $response = $req->send();
        $response_body = $response->getBody();
        $this->assertNotEquals('', $response_body, 'Response is empty');

        $x = simplexml_load_string($response_body);
        $ns = $x->getDocNamespaces();
        $x->registerXPathNamespace('ns', reset($ns));

        $elements = $x->xpath(
            '//ns:link[@rel="alternate" and @type="application/rss+xml"]'
        );
        $this->assertEquals(
            2, count($elements), 'Number of Links in Head not correct'
        );
        $this->assertContains('privateKey=', (string)$elements[1]['href']);
    }//end testVerifyPrivateRSSLinkExists



    /**
     * Test that the private RSS link doesn't exists when a user
     * does not have a private key or is not enabled
     */
    public function testVerifyPrivateRSSLinkDoesNotExist()
    {
        list($req, $uId) = $this->getLoggedInRequest('?unittestMode=1', true);

        $user = $this->us->getUser($uId);
        $req->setUrl($this->getTestUrl('/' . $user['username']));
        $response = $req->send();
        $response_body = $response->getBody();
        $this->assertNotEquals('', $response_body, 'Response is empty');

        $x = simplexml_load_string($response_body);
        $ns = $x->getDocNamespaces();
        $x->registerXPathNamespace('ns', reset($ns));

        $elements = $x->xpath(
            '//ns:link[@rel="alternate" and @type="application/rss+xml"]'
        );
        $this->assertEquals(
            1, count($elements), 'Number of Links in Head not correct'
        );
        $this->assertNotContains('privateKey=', (string)$elements[0]['href']);
    }//end testVerifyPrivateRSSLinkDoesNotExist



    /**
     * We once had the bug that URLs with special characters were escaped too
     * often. & -> &amp;
     */
    public function testAddressEncoding()
    {
        $this->addBookmark(null, 'http://example.org?foo&bar=baz');

        //get rid of bookmarks.php
        $this->url = $GLOBALS['unittestUrl'];

        $html = $this->getRequest()->send()->getBody();
        $x = simplexml_load_string($html);
        $ns = $x->getDocNamespaces();
        $x->registerXPathNamespace('ns', reset($ns));

        $elements = $x->xpath('//ns:a[@class="taggedlink"]');
        $this->assertEquals(
            1, count($elements), 'Number of links is not 1'
        );
        $this->assertEquals(
            'http://example.org?foo&bar=baz',
            (string)$elements[0]['href']
        );
    }

}//end class www_bookmarksTest
?>
