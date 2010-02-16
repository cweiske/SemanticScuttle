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

require_once 'PHPUnit/Framework.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__);

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
     *
     * @return integer ID of bookmark
     *
     * @see SemanticScuttle_Service_Bookmark::addBookmark()
     */
    protected function addBookmark(
        $user = null, $address = null, $status = 0
    ) {
        if ($user === null) {
            $user = $this->addUser();
        }

        $bs   = SemanticScuttle_Service_Factory::get('Bookmark');
        $rand = rand();

        if ($address === null) {
            $address = 'http://example.org/' . $rand;
        }

        $bid  = $bs->addBookmark(
            $address,
            'unittest bookmark #' . $rand,
            'description',
            null,
            $status,
            array('unittest'),
            null, null, false, false,
            $user
        );
        return $bid;
    }



    /**
     * Creates a new user in the database.
     *
     * @return integer ID of user
     */
    protected function addUser()
    {
        $us   = SemanticScuttle_Service_Factory::get('User');
        $rand = rand();
        $uid  = $us->addUser(
            'unittestuser-' . $rand,
            $rand,
            'unittest-' . $rand . '@example.org'
        );
        return $uid;
    }

}

?>