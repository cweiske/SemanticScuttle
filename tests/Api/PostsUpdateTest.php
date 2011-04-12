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
require_once 'HTTP/Request2.php';

/**
 * Unit tests for the SemanticScuttle last-update time API.
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Benjamin Huynh-Kim-Bang <mensonge@users.sourceforge.net>
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @author   Eric Dane <ericdane@users.sourceforge.net>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */
class Api_PostsUpdateTest extends TestBaseApi
{
    protected $urlPart = 'api/posts/update';



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
     * See if posts/update behaves correct if there is one bookmark
     */
    public function testPostUpdateOneBookmark()
    {
        $this->bs->deleteAll();

        list($req, $uId) = $this->getAuthRequest();
        $bId = $this->addBookmark(
            $uId, 'http://example.org/tag1', 0,
            array('unittest', 'tag1')
        );

        $data = $this->bs->getBookmarks(0, null, $uId);
        $this->assertEquals(1, $data['total']);
        $bookmark = $data['bookmarks'][0];

        //send request
        $res = $req->send();

        $this->assertEquals(200, $res->getStatus());
        //verify MIME content type
        $this->assertEquals(
            'text/xml; charset=utf-8',
            $res->getHeader('content-type')
        );

        //verify xml
        $this->assertTag(
            array(
                'tag'        => 'update',
                'attributes' => array(
                    'inboxnew' => '0'
                )
            ),
            $res->getBody(),
            '', false
        );
        //check time
        $xml = simplexml_load_string($res->getBody());
        $this->assertTrue(isset($xml['time']));
        $this->assertEquals(
            strtotime($bookmark['bDatetime']),
            strtotime(
                (string)$xml['time']
            )
        );
    }

}
?>