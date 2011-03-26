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
        $this->assertEquals(200, $res->getStatus());
        $this->assertEquals(
            'application/json; charset=utf-8',
            $res->getHeader('content-type')
        );
        $data = json_decode($res->getBody());
        $this->assertInternalType('array', $data);
        $this->assertEquals(2, count($data));
        $this->assertContains('admintag', $data);
        $this->assertContains('admintag2', $data);
    }

}


?>