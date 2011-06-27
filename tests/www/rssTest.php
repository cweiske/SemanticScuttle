<?php
require_once dirname(__FILE__) . '/../prepare.php';
require_once 'HTTP/Request2.php';

class www_rssTest extends TestBaseApi
{
    protected $urlPart = 'rss.php';


    /**
     * Verifies that the given number of feed items exist in the feed
     * XML tree.
     *
     * @var SimpleXMLElement $simpleXml RSS feed root element
     * @var integer          $nCount    Number of expected feed items
     * @var string           $msg       Error message
     */
    protected function assertItemCount(
        SimpleXMLElement $simpleXml, $nCount, $msg = null
    ) {
        $this->assertEquals($nCount, count($simpleXml->channel->item), $msg);
    }




    /**
     * A private bookmark should not show up in the global rss feed if the
     * user is not logged in nor passes the private key
     */
    public function testAllPrivateBookmarkNotLoggedIn()
    {
        list($uId, $username) = $this->addUserData();
        $this->addBookmark(
            $uId, null, SemanticScuttle_Model_Bookmark::SPRIVATE
        );

        $req = $this->getRequest();
        $response_body = $req->send()->getBody();

        $rss = simplexml_load_string($response_body);
        $this->assertItemCount($rss, 0, 'I see a private bookmark');
    }



    /**
     * A private bookmark should not show up in the user's rss feed if the
     * user is not logged in nor passes the private key
     */
    public function testUserPrivateBookmarkNotLoggedIn()
    {
        list($uId, $username) = $this->addUserData();
        $this->addBookmark(
            $uId, null, SemanticScuttle_Model_Bookmark::SPRIVATE
        );

        $req = $this->getRequest('/' . $username);
        $response_body = $req->send()->getBody();

        $rss = simplexml_load_string($response_body);
        $this->assertItemCount($rss, 0, 'I see a private bookmark');
    }




    /**
     * Test the global feed by passing the private key
     */
    public function testAllPrivateBookmarkWithPrivateKey()
    {
        list($uId, $username, $password, $privateKey) = $this->addUserData(
            null, null, true
        );
        $this->addBookmark(
            $uId, null, SemanticScuttle_Model_Bookmark::SPRIVATE,
            null, 'private bookmark'
        );

        $req = $this->getRequest('?privateKey=' . $privateKey);
        $response_body = $req->send()->getBody();

        $rss = simplexml_load_string($response_body);
        $this->assertItemCount($rss, 1, 'I miss the private bookmark');
        $this->assertEquals(
            'private bookmark', (string)$rss->channel->item[0]->title
        );
    }



    /**
     * Test the user feed by passing the private key
     */
    public function testUserPrivateBookmarkWithPrivateKey()
    {
        list($uId, $username, $password, $privateKey) = $this->addUserData(
            null, null, true
        );
        $this->addBookmark(
            $uId, null, SemanticScuttle_Model_Bookmark::SPRIVATE,
            null, 'private bookmark'
        );

        $req = $this->getRequest('/' . $username . '?privateKey=' . $privateKey);
        $response_body = $req->send()->getBody();

        $rss = simplexml_load_string($response_body);
        $this->assertItemCount($rss, 1, 'I miss the private bookmark');
        $this->assertEquals(
            'private bookmark', (string)$rss->channel->item[0]->title
        );
    }



    /**
     * Verify that fetching the feed with a private key
     * does not keep you logged in
     */
    public function testUserPrivateKeyDoesNotKeepLoggedYouIn()
    {
        list($uId, $username, $password, $privateKey) = $this->addUserData(
            null, null, true
        );
        $this->addBookmark(
            $uId, null, SemanticScuttle_Model_Bookmark::SPRIVATE,
            null, 'private bookmark'
        );

        $req = $this->getRequest('/' . $username . '?privateKey=' . $privateKey);
        $cookies = $req->setCookieJar()->getCookieJar();
        $response_body = $req->send()->getBody();

        $rss = simplexml_load_string($response_body);
        $items = $rss->channel->item;

        $this->assertEquals(1, count($items), 'I miss the private bookmark');
        $this->assertEquals('private bookmark', (string)$items[0]->title);

        //request the feed again, with the same cookies
        $req = $this->getRequest('/' . $username);
        $req->setCookieJar($cookies);
        $response_body = $req->send()->getBody();
        $rss = simplexml_load_string($response_body);
        $this->assertItemCount($rss, 0, 'I still see the private bookmark');
    }

}//end class www_rssTest
?>
