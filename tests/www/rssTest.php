<?php
require_once dirname(__FILE__) . '/../prepare.php';
require_once 'HTTP/Request2.php';

class www_rssTest extends TestBaseApi
{
    protected $urlPart = 'rss.php';

    /**
     * Test a user who does not have RSS private key enabled
     * and with a private bookmark.
     */
    public function testNoRSSPrivateKeyEnabled()
    {
        $this->setUnittestConfig(
            array('defaults' => array('privacy' => 2))
        );

        /* create user without RSS private Key */
        list($req, $uId) = $this->getLoggedInRequest(null, true, false, false);

        /* create private bookmark */
        $this->bs->addBookmark(
            'http://test', 'test', 'desc', 'note',
            2,//private
            array(), null, null, false, false, $uId
        );
        /* create public bookmark */
        $this->bs->addBookmark(
            'http://example.org', 'title', 'desc', 'priv',
            0,//public
            array(), null, null, false, false, $uId
        );

        /* get user details */
        $user = $this->us->getUser($uId);

        $req->setMethod(HTTP_Request2::METHOD_POST);
        $req->setUrl($this->getTestUrl('/' . $user['username'] . '?sort=date_desc'));
        $response = $req->send();
        $response_body = $response->getBody();

        $rss = simplexml_load_string($response_body);
        $items = $rss->channel->item;

        $this->assertEquals(1, count($items), 'Incorrect Number of RSS Items');
        $this->assertEquals('title', (string)$items[0]->title);
    }//end testNoRSSPrivateKeyEnabled


    /**
     * Test a user who has RSS private key setup
     * with private bookmark.
     */
    public function testRSSPrivateKeyEnabled()
    {
        $this->setUnittestConfig(
            array('defaults' => array('privacy' => 2))
        );

        /* create user with RSS private Key */
        list($req, $uId) = $this->getLoggedInRequest(null, true, false, true);

        /* create private bookmark */
        $this->bs->addBookmark(
            'http://test', 'test', 'desc', 'note',
            2,//private
            array(), null, null, false, false, $uId
        );
        /* create public bookmark */
        $this->bs->addBookmark(
            'http://example.org', 'title', 'desc', 'priv',
            0,//public
            array(), null, null, false, false, $uId
        );

        /* get user details */
        $user = $this->us->getUser($uId);

        $req->setMethod(HTTP_Request2::METHOD_POST);
        $req->setUrl($this->getTestUrl('/' . $user['username'] . '?sort=date_desc&privatekey=' . $user['privatekey']));
        $response = $req->send();
        $response_body = $response->getBody();

        $rss = simplexml_load_string($response_body);
        $items = $rss->channel->item;

        $this->assertEquals(2, count($items), 'Incorrect Number of RSS Items');
    }//end testRSSPrivateKeyEnabled



}//end class www_rssTest
?>
