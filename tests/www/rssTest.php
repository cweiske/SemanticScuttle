<?php
require_once dirname(__FILE__) . '/../prepare.php';
require_once 'HTTP/Request2.php';

class www_rssTest extends TestBaseApi
{
    protected $urlPart = 'rss.php';

    /**
     * A private bookmark should not show up in an rss feed if the
     * user is not logged in nor passes the private key
     */
    public function testPrivateNotLoggedIn()
    {
        list($uId, $username) = $this->addUserData();
        $this->addBookmark(
            $uId, null, SemanticScuttle_Model_Bookmark::SPRIVATE
        );

        $req = $this->getRequest('/' . $username);
        $response_body = $req->send()->getBody();

        $rss = simplexml_load_string($response_body);
        $items = $rss->channel->item;

        $this->assertEquals(0, count($items), 'I see a private bookmark');
    }//end testNoRSSPrivateKeyEnabled


    /**
     * Test a user who has RSS private key setup
     * with private bookmark.
     */
    public function testRSSPrivateKeyEnabled()
    {
        list($uId, $username, $password, $privateKey) = $this->addUserData(
            null, null, true
        );
        $this->addBookmark(
            $uId, null, SemanticScuttle_Model_Bookmark::SPRIVATE,
            null, 'private bookmark'
        );

        $req = $this->getRequest('/' . $username . '?privatekey=' . $privateKey);
        $response_body = $req->send()->getBody();

        $rss = simplexml_load_string($response_body);
        $items = $rss->channel->item;

        $this->assertEquals(1, count($items), 'I miss the private bookmark');
        $this->assertEquals('private bookmark', (string)$items[0]->title);
    }//end testRSSPrivateKeyEnabled



}//end class www_rssTest
?>
