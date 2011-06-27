<?php
require_once dirname(__FILE__) . '/../prepare.php';
require_once 'HTTP/Request2.php';

class www_indexTest extends TestBaseApi
{
    protected $urlPart = '';

    /**
     * Test that the private rss feed exists when user is setup
     * with a private key and is enabled
     */
    public function testVerifyPrivateRSSLinkExists()
    {
        list($req, $uId) = $this->getLoggedInRequest('?unittestMode=1', true, true);

        $user = $this->us->getUser($uId);
        $response = $req->send();
        $response_body = $response->getBody();

        $this->assertNotEquals('', $response_body, 'Response is empty');

        $x = simplexml_load_string($response_body);
        $ns = $x->getDocNamespaces();
        $x->registerXPathNamespace('ns', reset($ns));

        $elements = $x->xpath('//ns:link[@rel="alternate" and @type="application/rss+xml"]');
        $this->assertEquals(2, count($elements), 'Number of Links in Head not correct');
        $this->assertContains('privateKey=', (string)$elements[1]['href']);
    }//end testVerifyPrivateRSSLinkExists



    /**
     * Test that the private RSS link doesn't exists when a user
     * does not have a private key, or the private key is not enabled
     */
    public function testVerifyPrivateRSSLinkDoesNotExist()
    {
        list($req, $uId) = $this->getLoggedInRequest('?unittestMode=1', true);

        $user = $this->us->getUser($uId);
        $response = $req->send();
        $response_body = $response->getBody();
        $this->assertNotEquals('', $response_body, 'Response is empty');

        $x = simplexml_load_string($response_body);
        $ns = $x->getDocNamespaces();
        $x->registerXPathNamespace('ns', reset($ns));

        $elements = $x->xpath('//ns:link[@rel="alternate" and @type="application/rss+xml"]');
        $this->assertEquals(1, count($elements), 'Number of Links in Head not correct');
        $this->assertNotContains('privateKey=', (string)$elements[0]['href']);
    }//end testVerifyPrivateRSSLinkDoesNotExist


}//end class www_bookmarksTest
?>
