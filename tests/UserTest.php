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
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'UserTest::main');
}

require_once 'prepare.php';

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
        $this->us->deleteAll();
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
     * Test userEmailCombinationValid() with valid user
     * and valid email address.
     *
     * @return void
     */
    public function testUserEmailCombinationValid()
    {
        $this->us->deleteAll();

        $uid   = $this->addUser();
        $user  = $this->us->getUser($uid);
        $email = $user['email'];
        $name  = $user['username'];
        $this->assertTrue(
            $this->us->userEmailCombinationValid(
                $name, $email
            )
        );
    }



    /**
     * Test userEmailCombinationValid() with valid user and invalid email.
     *
     * @return void
     */
    public function testUserEmailCombinationValidInvalidEmail()
    {
        $this->us->deleteAll();

        $uid   = $this->addUser();
        $user  = $this->us->getUser($uid);
        $email = $user['email'];
        $name  = $user['username'];
        $this->assertFalse(
            $this->us->userEmailCombinationValid(
                $name, 'not-' . $email
            )
        );
    }



    /**
     * Test userEmailCombinationValid() with invalid user and invalid email.
     *
     * @return void
     */
    public function testUserEmailCombinationValidBothInvalid()
    {
        $this->us->deleteAll();

        $uid   = $this->addUser();
        $user  = $this->us->getUser($uid);
        $email = $user['email'];
        $name  = $user['username'];
        $this->assertFalse(
            $this->us->userEmailCombinationValid(
                'not-' . $name, 'not-' . $email
            )
        );
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



    /**
     * Check if getObjectUsers() without any user works
     *
     * @return void
     */
    public function testGetObjectUsersNone()
    {
        $users = $this->us->getObjectUsers();
        $this->assertEquals(0, count($users));
    }



    /**
     * Check if getObjectUsers() with a single user works
     *
     * @return void
     */
    public function testGetObjectUsersSingle()
    {
        $uid = $this->addUser();
        $users = $this->us->getObjectUsers();
        $this->assertEquals(1, count($users));
        $this->assertInstanceOf('SemanticScuttle_Model_User', reset($users));
    }



    /**
     * Check if getObjectUsers() with a several users works
     *
     * @return void
     */
    public function testGetObjectUsersMultiple()
    {
        $uid = $this->addUser();
        $uid2 = $this->addUser();
        $uid3 = $this->addUser();
        $users = $this->us->getObjectUsers();
        $this->assertEquals(3, count($users));
        $this->assertInstanceOf('SemanticScuttle_Model_User', reset($users));
    }



    /**
     * Test if the email validation function works
     *
     * @return void
     */
    public function testIsValidEmail()
    {
        $this->assertTrue(
            $this->us->isValidEmail('foo@example.org')
        );
        $this->assertTrue(
            $this->us->isValidEmail('foo-bar@semantic-scuttle.example.net')
        );
        $this->assertTrue(
            $this->us->isValidEmail('2334ABC@302.example.org')
        );

        $this->assertFalse(
            $this->us->isValidEmail('302.example.org')
        );
        $this->assertFalse(
            $this->us->isValidEmail('foo@302')
        );
        $this->assertFalse(
            $this->us->isValidEmail('foo@example!org')
        );
        $this->assertFalse(
            $this->us->isValidEmail('foo@@example.org')
        );
        $this->assertFalse(
            $this->us->isValidEmail('f@oo@example.org')
        );
    }

}


if (PHPUnit_MAIN_METHOD == 'UserTest::main') {
    UserTest::main();
}

?>