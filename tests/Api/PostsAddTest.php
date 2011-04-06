<?php
/**
 * SemanticScuttle - your social bookmark manager.
 *
 * PHP version 5.
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Benjamin Huynh-Kim-Bang <mensonge@users.sourceforge.net>
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @author   Eric Dane <ericdane@users.sourceforge.net>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */

require_once dirname(__FILE__) . '/../prepare.php';
require_once 'HTTP/Request2.php';

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Api_PostsAddTest::main');
}

/**
 * Unit tests for the SemanticScuttle post addition API.
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Benjamin Huynh-Kim-Bang <mensonge@users.sourceforge.net>
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @author   Eric Dane <ericdane@users.sourceforge.net>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */
class Api_PostsAddTest extends TestBaseApi
{
    protected $urlPart = 'api/posts/add';



    /**
     * Used to run this test class standalone
     *
     * @return void
     */
    public static function main()
    {
        require_once 'PHPUnit/TextUI/TestRunner.php';
        PHPUnit_TextUI_TestRunner::run(
            new PHPUnit_Framework_TestSuite(__CLASS__)
        );
    }


    public function setUp()
    {
        parent::setUp();
        $this->bs->deleteAll();
    }



    /**
     * Test if authentication is required when sending no auth data
     */
    public function testAuthWithoutAuthData()
    {
        $req = $this->getRequest(null, false);
        $res = $req->send();
        $this->assertEquals(401, $res->getStatus());
    }



    /**
     * Test if authentication is required when sending wrong user data
     */
    public function testAuthWrongCredentials()
    {
        $req = $this->getRequest(null, false);
        $req->setAuth('user', 'password', HTTP_Request2::AUTH_BASIC);
        $res = $req->send();
        $this->assertEquals(401, $res->getStatus());
    }



    /**
     * Test if adding a bookmark via POST works.
     */
    public function testAddBookmarkPost()
    {
        $bmUrl         = 'http://example.org/tag-1';
        $bmTags        = array('foo', 'bar', 'baz');
        $bmDatetime    = '2010-09-08T03:02:01Z';
        $bmTitle       = 'This is a foo title';
        $bmDescription = <<<TXT
This is the description of
my bookmark with some
newlines and <some>?&\$ÄÖ'"§special"'
characters
TXT;

        list($req, $uId) = $this->getAuthRequest();
        $req->setMethod(HTTP_Request2::METHOD_POST);
        $req->addPostParameter('url', $bmUrl);
        $req->addPostParameter('description', $bmTitle);
        $req->addPostParameter('extended', $bmDescription);
        $req->addPostParameter('tags', implode(' ', $bmTags));
        $req->addPostParameter('dt', $bmDatetime);
        $res = $req->send();

        //all should be well
        $this->assertEquals(200, $res->getStatus());
        //verify MIME content type
        $this->assertEquals(
            'text/xml; charset=utf-8',
            $res->getHeader('content-type')
        );

        //verify xml
        $this->assertTag(
            array(
                'tag'        => 'result',
                'attributes' => array('code' => 'done')
            ),
            $res->getBody(),
            null, false
        );

        //user should have one bookmark now
        $data = $this->bs->getBookmarks(0, null, $uId);
        $this->assertEquals(1, $data['total']);
        $bm = $data['bookmarks'][0];

        $this->assertEquals($bmUrl, $bm['bAddress']);
        $this->assertEquals($bmTitle, $bm['bTitle']);
        $this->assertEquals($bmDescription, $bm['bDescription']);
        $this->assertEquals($bmTags, $bm['tags']);
        $this->assertEquals(
            gmdate('Y-m-d H:i:s', strtotime($bmDatetime)),
            $bm['bDatetime']
        );
    }



    /**
     * Test if adding a bookmark via GET works.
     */
    public function testAddBookmarkGet()
    {
        $bmUrl         = 'http://example.org/tag-1';
        $bmTags        = array('foo', 'bar', 'baz');
        $bmDatetime    = '2010-09-08T03:02:01Z';
        $bmTitle       = 'This is a foo title';
        $bmDescription = <<<TXT
This is the description of
my bookmark with some
newlines and <some>?&\$ÄÖ'"§special"'
characters
TXT;

        list($req, $uId) = $this->getAuthRequest(
            '?url=' . urlencode($bmUrl)
            . '&description=' . urlencode($bmTitle)
            . '&extended=' . urlencode($bmDescription)
            . '&tags=' . urlencode(implode(' ', $bmTags))
            . '&dt=' . urlencode($bmDatetime)
        );
        $res = $req->send();

        //all should be well
        $this->assertEquals(200, $res->getStatus());
        //verify MIME content type
        $this->assertEquals(
            'text/xml; charset=utf-8',
            $res->getHeader('content-type')
        );

        //verify xml
        $this->assertTag(
            array(
                'tag'        => 'result',
                'attributes' => array('code' => 'done')
            ),
            $res->getBody(),
            null, false
        );

        //user should have one bookmark now
        $data = $this->bs->getBookmarks(0, null, $uId);
        $this->assertEquals(1, $data['total']);
        $bm = $data['bookmarks'][0];

        $this->assertEquals($bmUrl, $bm['bAddress']);
        $this->assertEquals($bmTitle, $bm['bTitle']);
        $this->assertEquals($bmDescription, $bm['bDescription']);
        $this->assertEquals($bmTags, $bm['tags']);
        $this->assertEquals(
            gmdate('Y-m-d H:i:s', strtotime($bmDatetime)),
            $bm['bDatetime']
        );
    }

    /**
     * Verify that the URL and description/title are enough parameters
     * to add a bookmark.
     */
    public function testUrlDescEnough()
    {
        list($req, $uId) = $this->getAuthRequest();
        $req->setMethod(HTTP_Request2::METHOD_POST);
        $req->addPostParameter('url', 'http://example.org/tag2');
        $req->addPostParameter('description', 'foo bar');
        $res = $req->send();

        //all should be well
        $this->assertEquals(200, $res->getStatus());
        //verify MIME content type
        $this->assertEquals(
            'text/xml; charset=utf-8',
            $res->getHeader('content-type')
        );

        //verify xml
        $this->assertTag(
            array(
                'tag'        => 'result',
                'attributes' => array('code' => 'done')
            ),
            $res->getBody(),
            null, false
        );

        //user has 1 bookmark now
        $data = $this->bs->getBookmarks(0, null, $uId);
        $this->assertEquals(1, $data['total']);
    }

    /**
     * Verify that the URL is required
     */
    public function testUrlRequired()
    {
        list($req, $uId) = $this->getAuthRequest();
        $req->setMethod(HTTP_Request2::METHOD_POST);
        //$req->addPostParameter('url', 'http://example.org/tag2');
        $req->addPostParameter('description', 'foo bar');
        $res = $req->send();

        //all should be well
        $this->assertEquals(400, $res->getStatus());
        //verify MIME content type
        $this->assertEquals(
            'text/xml; charset=utf-8',
            $res->getHeader('content-type')
        );

        //verify xml
        $this->assertTag(
            array(
                'tag'        => 'result',
                'attributes' => array('code' => 'URL missing')
            ),
            $res->getBody(),
            null, false
        );

        //user still has 0 bookmarks
        $data = $this->bs->getBookmarks(0, null, $uId);
        $this->assertEquals(0, $data['total']);
    }

    /**
     * Verify that the description/title is required
     */
    public function testDescriptionRequired()
    {
        list($req, $uId) = $this->getAuthRequest();
        $req->setMethod(HTTP_Request2::METHOD_POST);
        $req->addPostParameter('url', 'http://example.org/tag2');
        $res = $req->send();

        //all should be well
        $this->assertEquals(400, $res->getStatus());
        //verify MIME content type
        $this->assertEquals(
            'text/xml; charset=utf-8',
            $res->getHeader('content-type')
        );

        //verify xml
        $this->assertTag(
            array(
                'tag'        => 'result',
                'attributes' => array('code' => 'Description missing')
            ),
            $res->getBody(),
            null, false
        );

        //user still has 0 bookmarks
        $data = $this->bs->getBookmarks(0, null, $uId);
        $this->assertEquals(0, $data['total']);
    }

    /**
     * Test that the replace=no parameter prevents the bookmark from being
     * overwritten.
     */
    public function testReplaceNo()
    {
        $url    = 'http://example.org/tag2';
        $title1 = 'foo bar 1';
        $title2 = 'bar 2 foo';

        list($req, $uId) = $this->getAuthRequest();
        $req->setMethod(HTTP_Request2::METHOD_POST);
        $req->addPostParameter('url', $url);
        $req->addPostParameter('description', $title1);
        $res = $req->send();

        //all should be well
        $this->assertEquals(200, $res->getStatus());

        //send it a second time, with different title
        list($req, $dummy) = $this->getAuthRequest();
        $req->setMethod(HTTP_Request2::METHOD_POST);
        $req->addPostParameter('url', $url);
        $req->addPostParameter('description', $title2);
        $req->addPostParameter('replace', 'no');
        $res = $req->send();

        //this time we should get an error
        $this->assertEquals(409, $res->getStatus());
        //verify MIME content type
        $this->assertEquals(
            'text/xml; charset=utf-8',
            $res->getHeader('content-type')
        );

        //verify xml
        $this->assertTag(
            array(
                'tag'        => 'result',
                'attributes' => array('code' => 'bookmark does already exist')
            ),
            $res->getBody(),
            null, false
        );

        //user still has 1 bookmark now
        $data = $this->bs->getBookmarks(0, null, $uId);
        $this->assertEquals(1, $data['total']);
        $this->assertEquals($title1, $data['bookmarks'][0]['bTitle']);

        //send it a third time, without the replace parameter
        // it defaults to "no", so the bookmark should not get overwritten
        list($req, $dummy) = $this->getAuthRequest();
        $req->setMethod(HTTP_Request2::METHOD_POST);
        $req->addPostParameter('url', $url);
        $req->addPostParameter('description', $title2);
        $res = $req->send();

        //this time we should get an error
        $this->assertEquals(409, $res->getStatus());

        //bookmark should not have changed
        $data = $this->bs->getBookmarks(0, null, $uId);
        $this->assertEquals(1, $data['total']);
        $this->assertEquals($title1, $data['bookmarks'][0]['bTitle']);
    }

    /**
     * Test that the replace=yes parameter causes the bookmark to be updated.
     */
    public function testReplaceYes()
    {
        $url    = 'http://example.org/tag2';
        $title1 = 'foo bar 1';
        $title2 = 'bar 2 foo';

        list($req, $uId) = $this->getAuthRequest();
        $req->setMethod(HTTP_Request2::METHOD_POST);
        $req->addPostParameter('url', $url);
        $req->addPostParameter('description', $title1);
        $res = $req->send();

        //all should be well
        $this->assertEquals(200, $res->getStatus());

        //send it a second time, with different title
        list($req, $dummy) = $this->getAuthRequest();
        $req->setMethod(HTTP_Request2::METHOD_POST);
        $req->addPostParameter('url', $url);
        $req->addPostParameter('description', $title2);
        $req->addPostParameter('replace', 'yes');
        $res = $req->send();

        //no error
        $this->assertEquals(200, $res->getStatus());
        //verify MIME content type
        $this->assertEquals(
            'text/xml; charset=utf-8',
            $res->getHeader('content-type')
        );

        //verify xml
        $this->assertTag(
            array(
                'tag'        => 'result',
                'attributes' => array('code' => 'done')
            ),
            $res->getBody(),
            null, false
        );

        //user still has 1 bookmark now, but with the new title
        $data = $this->bs->getBookmarks(0, null, $uId);
        $this->assertEquals(1, $data['total']);
        $this->assertEquals($title2, $data['bookmarks'][0]['bTitle']);
    }
}

if (PHPUnit_MAIN_METHOD == 'Api_PostsAddTest::main') {
    Api_PostsAddTest::main();
}
?>