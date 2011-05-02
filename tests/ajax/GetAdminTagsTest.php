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
 * Unit tests for the ajax getadmintags.php script
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */
class ajax_GetAdminTagsTest extends TestBaseApi
{
    protected $urlPart = 'ajax/getadmintags.php';


    public function testTags()
    {
        list($user1, $uname1) = $this->addUserData();
        $user2 = $this->addUser();
        $this->addBookmark($user1, null, 0, array('admintag', 'admintag2'));
        $this->addBookmark($user2, null, 0, array('lusertag', 'lusertag2'));

        $this->setUnittestConfig(
            array(
                'admin_users' => array($uname1)
            )
        );

        $req = $this->getRequest('?unittestMode=1');
        $res = $req->send();
        $this->assertResponseJson200($res);
        $data = json_decode($res->getBody());
        $this->assertInternalType('array', $data);
        $this->assertEquals(2, count($data));
        $this->assertContains('admintag', $data);
        $this->assertContains('admintag2', $data);
    }

    public function testParameterBeginsWith()
    {
        list($user1, $uname1) = $this->addUserData();
        $this->addBookmark($user1, null, 0, array('foo', 'foobar', 'bar'));

        $this->setUnittestConfig(
            array(
                'admin_users' => array($uname1)
            )
        );

        $req = $this->getRequest('?unittestMode=1&beginsWith=foo');
        $res = $req->send();
        $data = json_decode($res->getBody());
        $this->assertResponseJson200($res);
        $this->assertInternalType('array', $data);
        $this->assertEquals(2, count($data));
        $this->assertContains('foo', $data);
        $this->assertContains('foobar', $data);
    }



    public function testParameterLimit()
    {
        list($user1, $uname1) = $this->addUserData();
        list($user2, $uname2) = $this->addUserData();
        $this->addBookmark($user1, null, 0, array('foo', 'foobar'));
        $this->addBookmark($user2, null, 0, array('foo', 'bar'));

        $this->setUnittestConfig(
            array(
                'admin_users' => array($uname1, $uname2)
            )
        );

        $req = $this->getRequest('?unittestMode=1&limit=1');
        $res = $req->send();
        $this->assertResponseJson200($res);
        $data = json_decode($res->getBody());
        $this->assertInternalType('array', $data);
        $this->assertEquals(1, count($data));
        $this->assertContains('foo', $data);

        $req = $this->getRequest('?unittestMode=1&limit=2');
        $res = $req->send();
        $this->assertResponseJson200($res);
        $data = json_decode($res->getBody());
        $this->assertInternalType('array', $data);
        $this->assertEquals(2, count($data));
        $this->assertContains('foo', $data);

        $req = $this->getRequest('?unittestMode=1&limit=3');
        $res = $req->send();
        $this->assertResponseJson200($res);
        $data = json_decode($res->getBody());
        $this->assertInternalType('array', $data);
        $this->assertEquals(3, count($data));
        $this->assertContains('foo', $data);
        $this->assertContains('foobar', $data);
        $this->assertContains('bar', $data);
    }

}


?>