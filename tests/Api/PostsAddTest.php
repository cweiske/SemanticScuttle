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
        $this->bs->deleteAll();

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
        $this->bs->deleteAll();

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

}

if (PHPUnit_MAIN_METHOD == 'Api_PostsAddTest::main') {
    Api_PostsAddTest::main();
}
?>