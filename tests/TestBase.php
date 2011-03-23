<?php
/**
 * SemanticScuttle - your social bookmark manager.
 *
 * PHP version 5.
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */

/**
 * Base unittest class that provides several helper methods.
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author Christian Weiske <cweiske@cweiske.de>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */
class TestBase extends PHPUnit_Framework_TestCase
{
    /**
     * Create a new bookmark.
     *
     * @param integer $user    User ID the bookmark shall belong
     * @param string  $address Bookmark address to use
     * @param integer $status  Bookmark visibility
     * @param array   $tags    Array of tags to attach. If "null" is given,
     *                         it will automatically be "unittest"
     * @param string  $title   Bookmark title
     * @param string  $date    strtotime-compatible string
     *
     * @return integer ID of bookmark
     *
     * @see SemanticScuttle_Service_Bookmark::addBookmark()
     */
    protected function addBookmark(
        $user = null, $address = null, $status = 0,
        $tags = null, $title = null, $date = null
    ) {
        if ($user === null) {
            $user = $this->addUser();
        }
        if ($tags === null) {
            $tags = array('unittest');
        }

        $bs   = SemanticScuttle_Service_Factory::get('Bookmark');
        $rand = rand();

        if ($address === null) {
            $address = 'http://example.org/' . $rand;
        }
        if ($title === null) {
            $title = 'unittest bookmark #' . $rand;
        }

        $bid  = $bs->addBookmark(
            $address,
            $title,
            'description',
            null,
            $status,
            $tags,
            null, $date, false, false,
            $user
        );
        return $bid;
    }



    /**
     * Creates a new user in the database.
     *
     * @param string $username Username
     * @param string $password Password
     *
     * @return integer ID of user
     */
    protected function addUser($username = null, $password = null)
    {
        $us   = SemanticScuttle_Service_Factory::get('User');
        $rand = rand();

        if ($username === null) {
            $username = 'unittestuser-' . $rand;
        }
        if ($password === null) {
            $password = $rand;
        }

        $uid  = $us->addUser(
            $username,
            $password,
            'unittest-' . $rand . '@example.org'
        );
        return $uid;
    }



    /**
     * Retrieves the UID of an admin user.
     * If that user does not exist in the database, it is created.
     *
     * @return integer UID of admin user
     */
    protected function getAdminUser()
    {
        if (count($GLOBALS['admin_users']) == 0) {
            $this->fail('No admin users configured');
        }
        $adminUserName = reset($GLOBALS['admin_users']);

        $us  = SemanticScuttle_Service_Factory::get('User');
        $uid = $us->getIdFromUser($adminUserName);
        if ($uid === null) {
            //that user does not exist in the database; create it
            $uid = $us->addUser(
                $adminUserName,
                rand(),
                'unittest-admin-' . $adminUserName . '@example.org'
            );
        }

        return $uid;
    }
}

?>