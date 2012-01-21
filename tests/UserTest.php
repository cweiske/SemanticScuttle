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
    protected function setUp()
    {
        $this->us = SemanticScuttle_Service_Factory::get('User');
        $this->us->deleteAll();
    }



    /**
     * @covers SemanticScuttle_Service_User::addUser
     */
    public function testAddUserPrivateKey()
    {
        $name = substr(md5(uniqid()), 0, 6);
        $pkey = 'my-privateKey';
        $id   = $this->us->addUser(
            $name, uniqid(), 'foo@example.org', $pkey
        );
        $this->assertNotEquals(false, $id);
        $this->assertInternalType('integer', $id);

        $arUser = $this->us->getUserByPrivateKey($pkey);
        $this->assertNotEquals(false, $arUser, 'user not found by private key');
        $this->assertEquals($id, $arUser['uId'], 'wrong user loaded');
    }


    /**
     * @covers SemanticScuttle_Service_User::updateUser
     */
    public function testUpdateUserFalseWhenIdNotNumeric()
    {
        $this->assertFalse(
            $this->us->updateUser('foo', null, null, null, null, null)
        );
    }


    /**
     * @covers SemanticScuttle_Service_User::updateUser
     */
    public function testUpdateUserPrivateKeyNewKeyEnabled()
    {
        $pkey = 'testUpdateUserPrivateKeyNewKey12';
        $uid  = $this->addUser();

        $this->assertTrue(
            $this->us->updateUser(
                $uid, 'password', 'name', 'test@example.org', '', '',
                $pkey, true
            )
        );
        $arUser = $this->us->getUser($uid);
        $this->assertInternalType('array', $arUser);
        $this->assertEquals($pkey, $arUser['privateKey']);
    }


    /**
     * @covers SemanticScuttle_Service_User::updateUser
     */
    public function testUpdateUserPrivateKeyNewKeyDisabled()
    {
        $pkey = 'testUpdateUserPrivateKeyNewKeyDi';
        $uid  = $this->addUser();

        $this->assertTrue(
            $this->us->updateUser(
                $uid, 'password', 'name', 'test@example.org', '', '',
                $pkey, false
            )
        );
        $arUser = $this->us->getUser($uid);
        $this->assertInternalType('array', $arUser);
        $this->assertEquals(
            '-' . $pkey, $arUser['privateKey'],
            'private key did not get disabled'
        );
    }


    /**
     * Passing an empty string / NULL as key but enabling it
     * should automatically create a new key.
     *
     * @covers SemanticScuttle_Service_User::updateUser
     */
    public function testUpdateUserPrivateKeyNoKeyEnabled()
    {
        $pkey = 'testUpdateUserPrivateKeyNoKeyEna';
        $uid  = $this->addUser();

        $this->assertTrue(
            $this->us->updateUser(
                $uid, 'password', 'name', 'test@example.org', '', '',
                null, true
            )
        );
        $arUser = $this->us->getUser($uid);
        $this->assertInternalType('array', $arUser);
        $this->assertNotEquals(
            '', $arUser['privateKey'], 'private key was not created'
        );
    }


    /**
     * Passing an empty string / NULL as key and disabling it
     * should keep no key
     *
     * @covers SemanticScuttle_Service_User::updateUser
     */
    public function testUpdateUserPrivateKeyNoKeyDisabled()
    {
        $pkey = 'testUpdateUserPrivateKeyNoKeyDis';
        $uid  = $this->addUser();

        $this->assertTrue(
            $this->us->updateUser(
                $uid, 'password', 'name', 'test@example.org', '', '',
                null, false
            )
        );
        $arUser = $this->us->getUser($uid);
        $this->assertInternalType('array', $arUser);
        $this->assertEquals(
            '', $arUser['privateKey'], 'private key was set'
        );
    }


    /**
     * Passing an empty string / NULL as key and disabling it
     * should keep no key
     *
     * @covers SemanticScuttle_Service_User::updateUser
     */
    public function testUpdateUserPrivateKeyExistingKeyEnabled()
    {
        $pkey = '12345678901234567890123456789012';
        $uid  = $this->addUser();

        $this->assertTrue(
            $this->us->updateUser(
                $uid, 'password', 'name', 'test@example.org', '', '',
                '-' . $pkey, true
            )
        );
        $arUser = $this->us->getUser($uid);
        $this->assertInternalType('array', $arUser);
        $this->assertEquals(
            $pkey, $arUser['privateKey'], 'private key was not enabled'
        );
    }

    //FIXME: verify I cannot re-use private key of different user



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

    public function testGetIdFromUserParamId()
    {
        $uid   = $this->addUser();
        $newId = $this->us->getIdFromUser($uid);
        $this->assertInternalType('integer', $newId);
        $this->assertEquals($uid, $newId);
    }

    public function testGetIdFromUserParamUsername()
    {
        $uid   = $this->addUser('someusername');
        $newId = $this->us->getIdFromUser('someusername');
        $this->assertInternalType('integer', $newId);
        $this->assertEquals($uid, $newId);
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


    public function testGetUserByPrivateKeyEmptyKey()
    {
        $arUser = $this->us->getUserByPrivateKey(null);
        $this->assertFalse($arUser);
    }


    public function testGetUserByPrivateKeyInvalid()
    {
        $arUser = $this->us->getUserByPrivateKey('foobar');
        $this->assertFalse($arUser);

        $arUser = $this->us->getUserByPrivateKey('%');
        $this->assertFalse($arUser);
    }


    public function testGetUserByPrivateKeyValidKey()
    {
        $pkey = $this->us->getNewPrivateKey();
        $uId = $this->addUser(null, null, $pkey);

        $arUser = $this->us->getUserByPrivateKey($pkey);
        $this->assertInternalType('array', $arUser);
        $this->assertArrayHasKey('uId', $arUser);
        $this->assertArrayHasKey('username', $arUser);

        $this->assertEquals($uId, $arUser['uId']);
    }


    /**
     * @covers SemanticScuttle_Service_User::privateKeyExists
     */
    public function testPrivateKeyExistsEmpty()
    {
        $this->assertFalse($this->us->privateKeyExists(null));
        $this->assertFalse($this->us->privateKeyExists(''));
    }


    /**
     * @covers SemanticScuttle_Service_User::privateKeyExists
     */
    public function testPrivateKeyExistsInvalid()
    {
        $this->assertFalse($this->us->privateKeyExists('-1'));
    }


    /**
     * @covers SemanticScuttle_Service_User::privateKeyExists
     */
    public function testPrivateKeyExists()
    {
        $randKey = $this->us->getNewPrivateKey();
        $this->assertFalse($this->us->privateKeyExists($randKey));
        $uid = $this->addUser(null, null, $randKey);

        $this->us->setCurrentUserId($uid);
        $this->assertEquals($uid, $this->us->getCurrentUserId());

        $this->assertTrue($this->us->privateKeyExists($randKey));
    }


    /**
     * @covers SemanticScuttle_Service_User::isPrivateKeyValid
     */
    public function testIsPrivateKeyValid()
    {
        $this->assertFalse(
            $this->us->isPrivateKeyValid(null),
            'NULL is an invalid private key'
        );

        $randKey = $this->us->getNewPrivateKey();
        $this->assertTrue(
            $this->us->isPrivateKeyValid($randKey),
            'generated key should be valid'
        );

        $randKey2 = '-'.$this->us->getNewPrivateKey();
        $this->assertFalse(
            $this->us->isPrivateKeyValid($randKey2),
            'disabled privateKey should return false'
        );
    }


    public function testLoginPrivateKeyInvalid()
    {
        /* normal user with enabled privateKey */
        $randKey = $this->us->getNewPrivateKey();
        $uid1 = $this->addUser('testusername', 'passw0rd', $randKey);
        /* user that has disabled privateKey */
        $randKey2 = '-'.$this->us->getNewPrivateKey();
        $uid2 = $this->addUser('seconduser', 'passw0RD', $randKey2);

        /* test invalid private key */
        $this->assertFalse(
            $this->us->loginPrivateKey('02848248084082408240824802408248')
        );
    }


    public function testLoginPrivateKeyValidEnabledKey()
    {
        /* normal user with enabled privateKey */
        $randKey = $this->us->getNewPrivateKey();
        $uid1 = $this->addUser('testusername', 'passw0rd', $randKey);
        /* user that has disabled privateKey */
        $randKey2 = '-'.$this->us->getNewPrivateKey();
        $uid2 = $this->addUser('seconduser', 'passw0RD', $randKey2);


        /* test valid credentials with private key enabled */
        $this->assertTrue(
            $this->us->loginPrivateKey($randKey)
        );
    }


    public function testLoginPrivateKeyInvalidEnabledKey()
    {
        /* normal user with enabled privateKey */
        $randKey = $this->us->getNewPrivateKey();
        $uid1 = $this->addUser('testusername', 'passw0rd', $randKey);
        /* user that has disabled privateKey */
        $randKey2 = '-'.$this->us->getNewPrivateKey();
        $uid2 = $this->addUser('seconduser', 'passw0RD', $randKey2);


        /* test valid credentials with private key enabled but invalid key */
        $this->assertFalse(
            $this->us->loginPrivateKey('123')
        );
    }


    public function testLoginPrivateKeyValidDisabledKey()
    {
        /* normal user with enabled privateKey */
        $randKey = $this->us->getNewPrivateKey();
        $uid1 = $this->addUser('testusername', 'passw0rd', $randKey);
        /* user that has disabled privateKey */
        $randKey2 = '-'.$this->us->getNewPrivateKey();
        $uid2 = $this->addUser('seconduser', 'passw0RD', $randKey2);

        /* confirm user exists so future fails should be due to randkey */
        $this->assertTrue(
            $this->us->login('seconduser', 'passw0RD', false)
        );

        /* test valid credentials with private key disabled */
        $this->assertFalse(
            $this->us->loginPrivateKey($randKey2)
        );
    }


    public function testLoginPrivateKeyInvalidDisabled()
    {
        /* normal user with enabled privateKey */
        $randKey = $this->us->getNewPrivateKey();
        $uid1 = $this->addUser('testusername', 'passw0rd', $randKey);
        /* user that has disabled privateKey */
        $randKey2 = '-'.$this->us->getNewPrivateKey();
        $uid2 = $this->addUser('seconduser', 'passw0RD', $randKey2);

        /* test valid credentials with private key disabled and invalid key */
        $this->assertFalse(
            $this->us->loginPrivateKey('-1')
        );
        $this->assertFalse(
            $this->us->loginPrivateKey(null)
        );
    }

}
?>
