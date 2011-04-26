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


    /**
     * Test that a default privacy setting of 2 (Private) is used in adding 
     * a bookmark. 
     */
    public function testDefaultPrivacyPrivate()
    {
        $this->setUnittestConfig(
            array('defaults' => array('privacy' => 2))
        );
        list($req, $uId) = $this->getAuthRequest('?unittestMode=1');
        $req->setMethod(HTTP_Request2::METHOD_POST);
        $req->addPostParameter('url', 'http://www.example.org/testdefaultprivacyposts_addprivate');
        $req->addPostParameter('description', 'Test bookmark 1 for default privacy.');
        $req->send();
        $this->us->setCurrentUserId($uId);
        $bms = $this->bs->getBookmarks(0, null, $uId);
        $this->assertEquals(1, count($bms['bookmarks']));
        $bm = reset($bms['bookmarks']);
        $this->assertEquals('2', $bm['bStatus']);
    }//end testDefaultPrivacyPrivate


    /**
     * Test that a default privacy setting of 0 (Public) is used in adding 
     * a bookmark. 
     */
    public function testDefaultPrivacyPublic()
    {
        $this->setUnittestConfig(
            array('defaults' => array('privacy' => 0))
        );
        list($req, $uId) = $this->getAuthRequest('?unittestMode=1');
        $req->setMethod(HTTP_Request2::METHOD_POST);
        $req->addPostParameter('url', 'http://www.example.org/testdefaultprivacyposts_addpublic');
        $req->addPostParameter('description', 'Test bookmark 1 for default privacy.');
        $req->send();
        $this->us->setCurrentUserId($uId);
        $bms = $this->bs->getBookmarks(0, null, $uId);
        $this->assertEquals(1, count($bms['bookmarks']));
        $bm = reset($bms['bookmarks']);
        $this->assertEquals('0', $bm['bStatus']);
    }//end testDefaultPrivacyPublic


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


    /**
     * Test that the default privacy setting is selected in the Privacy 
     * drop-down list when an existing bookmark is accessed with bookmarks.php
     * and the get action. 
     */
    public function testDefaultPrivacyBookmarksGet()
    {
        $this->setUnittestConfig(
            array('defaults' => array('privacy' => 2))
        );
        list($req, $uId) = $this->getLoggedInRequest();
        $req->setMethod(HTTP_Request2::METHOD_POST);
        $req->addPostParameter('url', 'http://www.example.org/testdefaultprivacyposts_bookmarksget');
        $req->addPostParameter('description', 'Test bookmark 1 for default privacy.');
        $req->addPostParameter('status', '0');
        $req->send();

        $bms = $this->bs->getBookmarks(0, null, $uId);
        $this->assertEquals(1, count($bms['bookmarks']));
        $bm = reset($bms['bookmarks']);
        $bmId = $bm['bId'];
        $oldUid = $uId;
        $user = $this->us->getUser($uId);
        $userId = $user['username'];
        $reqUrl = $GLOBALS['unittestUrl'] . 'bookmarks.php/' . $userId . '?action=get' . '&unittestMode=1'; 

        list($req, $uId) = $this->getAuthRequest('?unittestMode=1');
        $req->setMethod(HTTP_Request2::METHOD_POST);
        $req->setUrl($reqUrl);
        $testcookiekey = md5($GLOBALS['dbname'].$GLOBALS['tableprefix']).'-login';
        $userinfo = $this->us->getUser($oldUid);
        $testcookiepassword = $userinfo['password'];
        $testusername = $userinfo['username'];
        $testcookievalue = $oldUid . ':' . md5($testusername . $testcookiepassword);
        $req->setCookieJar(true);
        $req->addCookie($testcookiekey, $testcookievalue);
        $req->addPostParameter('submitted', '1');
        $response = $req->send();
        $response_body = $response->getBody();

        $x = simplexml_load_string($response_body);
        $ns = $x->getDocNamespaces();
        $x->registerXPathNamespace('ns', reset($ns));

        $elements = $x->xpath('//ns:select[@name="status"]/ns:option[@selected="selected"]');
        $this->assertEquals(1, count($elements), 'No selected status option found');
        $this->assertEquals(2, (string)$elements[0]['value']);
    }//end testDefaultPrivacyBookmarksGet


    /**
     * Test that the default privacy setting is selected in the Privacy 
     * drop-down list when an existing bookmark is accessed with bookmarks.php
     * and the add action. 
     */
    public function testDefaultPrivacyBookmarksAdd()
    {
        $this->setUnittestConfig(
            array('defaults' => array('privacy' => 1))
        );
        list($req, $uId) = $this->getAuthRequest('?unittestMode=1');
        $req->setMethod(HTTP_Request2::METHOD_POST);
        $req->addPostParameter('url', 'http://www.example.org/testdefaultprivacyposts_bookmarksadd');
        $req->addPostParameter('description', 'Test bookmark 2 for default privacy.');
        $req->addPostParameter('status', '0');
        $req->send();
        $bms = $this->bs->getBookmarks(0, null, $uId);
        $this->assertEquals(1, count($bms['bookmarks']));
        $bm = reset($bms['bookmarks']);
        $bmId = $bm['bId'];
        $oldUid = $uId;
        $user = $this->us->getUser($uId);
        $userId = $user['username'];
        $reqUrl = $GLOBALS['unittestUrl'] . 'bookmarks.php/' . $userId . '?action=add' . '&unittestMode=1'; 
        list($req, $uId) = $this->getAuthRequest('?unittestMode=1');
        $req->setMethod(HTTP_Request2::METHOD_POST);
        $req->setUrl($reqUrl);
        $testcookiekey = md5($GLOBALS['dbname'].$GLOBALS['tableprefix']).'-login';
        $userinfo = $this->us->getUser($oldUid);
        $testcookiepassword = $userinfo['password'];
        $testusername = $userinfo['username'];
        $testcookievalue = $oldUid . ':' . md5($testusername . $testcookiepassword);
        $req->setCookieJar(true);
        $req->addCookie($testcookiekey, $testcookievalue);
        $req->addPostParameter('submitted', '1');
        $response = $req->send();
        $response_body = $response->getBody();
        $start = strpos($response_body, 'Privacy');
        $end = strpos($response_body, 'referrer');
        $length = $end - $start;
        $response_body = substr($response_body, $start, $length);
        $start = strpos($response_body, 'selected');
        $start = $start - 3;
        $length = 1;
        $selected_privacy = substr($response_body, $start, $length);
        $this->assertEquals('1', $selected_privacy);
    }//end testDefaultPrivacyBookmarksAdd


}

if (PHPUnit_MAIN_METHOD == 'Api_PostsAddTest::main') {
    Api_PostsAddTest::main();
}
?>
