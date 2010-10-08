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
    define('PHPUnit_MAIN_METHOD', 'ajax_GetAdminLinkedTagsTest::main');
}

/**
 * Unit tests for the ajax linked admin tags script
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */
class ajax_GetAdminLinkedTagsTest extends TestBaseApi
{
    protected $urlPart = 'ajax/getadminlinkedtags.php';



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
     * Verify that we get the configured root tags if
     * we do not pass any parameters
     */
    public function testRootTags()
    {
        $req = $this->getRequest();
        $res = $req->send();

        $this->assertEquals(200, $res->getStatus());
        $this->assertEquals(
            'application/json; charset=utf-8',
            $res->getHeader('content-type')
        );

        $data = json_decode($res->getBody());
        $this->assertType('array', $data);

        //same number of elements as the menu2Tags array
        $this->assertEquals(
            count($GLOBALS['menu2Tags']),
            count($data)
        );

        //and the same contents
        foreach ($data as $tagObj) {
            $tagName = $tagObj->data->title;
            $this->assertContains($tagName, $GLOBALS['menu2Tags']);
        }
    }

    /**
     * Verify that we get subtags of a given tag
     */
    public function testSubTags()
    {
        $t2t = SemanticScuttle_Service_Factory::get('Tag2Tag');
        $t2t->deleteAll();

        $menu2Tag = reset($GLOBALS['menu2Tags']);
        //we have a subtag now
        $this->addBookmark(
            $this->getAdminUser(),
            null,
            0,
            $menu2Tag . '>adminsubtag'
        );

        $res = $this->getRequest('?tag=' . $menu2Tag)->send();
        $this->assertEquals(200, $res->getStatus());
        $this->assertEquals(
            'application/json; charset=utf-8',
            $res->getHeader('content-type')
        );

        $data = json_decode($res->getBody());
        $this->assertType('array', $data);

        //only one subtag
        $this->assertEquals(1, count($data));
        $this->assertEquals('adminsubtag', $data[0]->data->title);
    }

    /**
     * Verify that we only get admin tags, not tags from
     * non-admin people
     */
    public function testOnlyAdminTags()
    {
        $t2t = SemanticScuttle_Service_Factory::get('Tag2Tag');
        $t2t->deleteAll();

        $menu2Tag = reset($GLOBALS['menu2Tags']);
        //we have a subtag now
        $this->addBookmark(
            $this->getAdminUser(),
            null,
            0,
            $menu2Tag . '>adminsubtag'
        );
        //add another bookmark now, but for a normal user
        $this->addBookmark(
            null,
            null,
            0,
            $menu2Tag . '>normalsubtag'
        );

        $res = $this->getRequest('?tag=' . $menu2Tag)->send();
        $this->assertEquals(200, $res->getStatus());
        $this->assertEquals(
            'application/json; charset=utf-8',
            $res->getHeader('content-type')
        );

        $data = json_decode($res->getBody());
        $this->assertType('array', $data);

        //we should have only one subtag now, the admin one
        $this->assertEquals(1, count($data));
        $this->assertEquals('adminsubtag', $data[0]->data->title);
    }
}

if (PHPUnit_MAIN_METHOD == 'ajax_GetAdminLinkedTagsTest::main') {
    ajax_GetAdminLinkedTagsTest::main();
}
?>