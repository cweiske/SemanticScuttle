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
 * Unit tests for the ajax getcontacttags.php script
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */
class ajax_GetContactTagsTest extends TestBaseApi
{
    protected $urlPart = 'ajax/getcontacttags.php';


    /**
     * If no user is logged in, no data are returned
     */
    public function testNoUserLoggedIn()
    {
        $res = $this->getRequest()->send();
        $this->assertEquals(200, $res->getStatus());
        $this->assertEquals(
            'application/json; charset=utf-8',
            $res->getHeader('content-type')
        );
        $data = json_decode($res->getBody());
        $this->assertInternalType('array', $data);
        $this->assertEquals(0, count($data));
    }


    public function testUserLoggedIn()
    {
        list($req, $uId) = $this->getAuthRequest();
        $this->addBookmark($uId, null, 0, array('public'));
        $this->addBookmark($uId, null, 1, array('shared'));
        $this->addBookmark($uId, null, 2, array('private'));
        
        $res = $req->send();
        $this->assertEquals(200, $res->getStatus());
        $this->assertEquals(
            'application/json; charset=utf-8',
            $res->getHeader('content-type')
        );
        $data = json_decode($res->getBody());
        $this->assertInternalType('array', $data);
        $this->assertEquals(3, count($data));
        $this->assertContains('public', $data);
        $this->assertContains('shared', $data);
        $this->assertContains('private', $data);
    }
}


?>