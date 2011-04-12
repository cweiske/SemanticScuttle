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
 * Unit tests for the SemanticScuttle post deletion API.
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Benjamin Huynh-Kim-Bang <mensonge@users.sourceforge.net>
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @author   Eric Dane <ericdane@users.sourceforge.net>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */
class Api_PostsDeleteTest extends TestBaseApi
{
    protected $urlPart = 'api/posts/delete';



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
     * Test if deleting an own bookmark works.
     */
    public function testDeleteOwnBookmark()
    {
        $this->bs->deleteAll();

        $bookmarkUrl = 'http://example.org/tag-1';

        list($req, $uId) = $this->getAuthRequest(
            '?url=' . urlencode($bookmarkUrl)
        );

        $bId = $this->addBookmark(
            $uId, $bookmarkUrl, 0,
            array('unittest', 'tag1')
        );
        //user has one bookmark now
        $data = $this->bs->getBookmarks(0, null, $uId);
        $this->assertEquals(1, $data['total']);

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
                'tag'        => 'result',
                'attributes' => array('code' => 'done')
            ),
            $res->getBody(),
            null, false
        );

        //bookmark should be deleted now
        $data = $this->bs->getBookmarks(0, null, $uId);
        $this->assertEquals(0, $data['total']);
    }



    /**
     * Test if deleting an own bookmark via POST works.
     */
    public function testDeleteOwnBookmarkPost()
    {
        $this->bs->deleteAll();

        $bookmarkUrl = 'http://example.org/tag-1';

        list($req, $uId) = $this->getAuthRequest();

        $bId = $this->addBookmark(
            $uId, $bookmarkUrl, 0,
            array('unittest', 'tag1')
        );
        //user has one bookmark now
        $data = $this->bs->getBookmarks(0, null, $uId);
        $this->assertEquals(1, $data['total']);

        //send request
        $req->setMethod(HTTP_Request2::METHOD_POST);
        $req->addPostParameter('url', $bookmarkUrl);
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
                'tag'        => 'result',
                'attributes' => array('code' => 'done')
            ),
            $res->getBody(),
            null, false
        );

        //bookmark should be deleted now
        $data = $this->bs->getBookmarks(0, null, $uId);
        $this->assertEquals(0, $data['total']);
    }



    /**
     * Verify that deleting a bookmark of a different does not work
     */
    public function testDeleteOtherBookmark()
    {
        $this->bs->deleteAll();

        $bookmarkUrl = 'http://example.org/tag-1';

        list($req, $uId) = $this->getAuthRequest(
            '?url=' . urlencode($bookmarkUrl)
        );
        $uId2 = $this->addUser();

        $bId = $this->addBookmark(
            $uId2, $bookmarkUrl, 0,
            array('unittest', 'tag1')
        );
        //user 1 has no bookmarks
        $data = $this->bs->getBookmarks(0, null, $uId);
        $this->assertEquals(0, $data['total']);
        //user 2 has one bookmark
        $data = $this->bs->getBookmarks(0, null, $uId2);
        $this->assertEquals(1, $data['total']);

        //send request
        $res = $req->send();

        //404 - user does not have that bookmark
        $this->assertEquals(404, $res->getStatus());

        //verify MIME content type
        $this->assertEquals(
            'text/xml; charset=utf-8',
            $res->getHeader('content-type')
        );

        //verify xml
        $this->assertTag(
            array(
                'tag'        => 'result',
                'attributes' => array('code' => 'item not found')
            ),
            $res->getBody(),
            '', false
        );

        //bookmark should still be there
        $data = $this->bs->getBookmarks(0, null, $uId2);
        $this->assertEquals(1, $data['total']);
    }



    /**
     * Test if deleting a bookmark works that also other users
     * bookmarked.
     */
    public function testDeleteBookmarkOneOfTwo()
    {
        $this->bs->deleteAll();

        $bookmarkUrl = 'http://example.org/tag-1';

        list($req, $uId) = $this->getAuthRequest(
            '?url=' . urlencode($bookmarkUrl)
        );
        $uId2 = $this->addUser();
        $uId3 = $this->addUser();

        //important: the order of addition is crucial here
        $this->addBookmark(
            $uId2, $bookmarkUrl, 0,
            array('unittest', 'tag1')
        );
        $bId = $this->addBookmark(
            $uId, $bookmarkUrl, 0,
            array('unittest', 'tag1')
        );
        $this->addBookmark(
            $uId3, $bookmarkUrl, 0,
            array('unittest', 'tag1')
        );

        //user one and two have a bookmark now
        $data = $this->bs->getBookmarks(0, null, $uId);
        $this->assertEquals(1, $data['total']);
        $data = $this->bs->getBookmarks(0, null, $uId2);
        $this->assertEquals(1, $data['total']);

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
                'tag'        => 'result',
                'attributes' => array('code' => 'done')
            ),
            $res->getBody(),
            '', false
        );

        //bookmark should be deleted now
        $data = $this->bs->getBookmarks(0, null, $uId);
        $this->assertEquals(0, $data['total']);
        //user 2 should still have his
        $data = $this->bs->getBookmarks(0, null, $uId2);
        $this->assertEquals(1, $data['total']);
        //user 3 should still have his, too
        $data = $this->bs->getBookmarks(0, null, $uId3);
        $this->assertEquals(1, $data['total']);
    }

}
?>