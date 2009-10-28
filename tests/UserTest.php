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

require_once 'prepare.php';

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'UserTest::main');
}

/**
 * Unit tests for the SemanticScuttle user service.
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Benjamin Huynh-Kim-Bang <mensonge@users.sourceforge.net>
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @author   Eric Dane <ericdane@users.sourceforge.net>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */
class UserTest extends TestBase
{



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



    protected function setUp()
    {
        $this->us = SemanticScuttle_Service_Factory::get('User');
    }



    /**
     * Test that setting the current user ID is permanent.
     * and that the current user array is the same ID
     *
     * @return void
     */
    public function testSetCurrentUserId()
    {
        $uid = $this->addUser();
        $uid2 = $this->addUser();

        $this->us->setCurrentUserId($uid);
        $this->assertEquals($uid, $this->us->getCurrentUserId());

        $user = $this->us->getCurrentUser();
        $this->assertEquals($uid, $user['uId']);
    }



    /**
     * Test that changing the current user also
     * changes the current user array
     *
     * @return void
     */
    public function testSetCurrentUserIdChange()
    {
        $uid = $this->addUser();
        $uid2 = $this->addUser();
        $this->assertNotEquals($uid, $uid2);

        $this->us->setCurrentUserId($uid);
        $this->assertEquals($uid, $this->us->getCurrentUserId());

        $user = $this->us->getCurrentUser();
        $this->assertEquals($uid, $user['uId']);

        //change it
        $this->us->setCurrentUserId($uid2);
        $this->assertEquals($uid2, $this->us->getCurrentUserId());

        $user = $this->us->getCurrentUser();
        $this->assertEquals($uid2, $user['uId']);
    }



    /**
     * Test login() function with invalid creditentials
     *
     * @return void
     */
    public function testLoginInvalid()
    {
        $this->us->deleteAll();
        $this->assertFalse(
            $this->us->login('doesnot', 'exist', false)
        );
    }

}


if (PHPUnit_MAIN_METHOD == 'UserTest::main') {
    UserTest::main();
}

?>