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
 * SemanticScuttle user object.
 * Rarely used fields are filled if required.
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Benjamin Huynh-Kim-Bang <mensonge@users.sourceforge.net>
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @author   Eric Dane <ericdane@users.sourceforge.net>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */
class SemanticScuttle_Model_User
{
    var $id;
    var $username;
    var $name;
    var $email;
    var $homepage;
    var $content;
    var $datetime;
    var $isAdmin;
    var $privateKey;

    /**
     * Create a new user object
     *
     * @param integer $id       User ID
     * @param string  $username Username
     */
    public function __construct($id, $username)
    {
        $this->id = $id;
        $this->username = $username;
    }

    /**
     * Returns user ID
     *
     * @return integer ID
     */
    public function getId()
    {
        return (int)$this->id;
    }

    /**
     * Returns logon user name
     *
     * @return string User name
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Returns private key
     *
     * @param boolean return sanitized value which basically drops
     *                leading dash if exists
     *
     * @return string private key
     */
    public function getPrivateKey($sanitized = false)
    {
        // Look for value only if not already set
        if (!isset($this->privateKey)) {
            $us = SemanticScuttle_Service_Factory::get('User');
            $user = $us->getUser($this->id);
            $this->privateKey = $user['privateKey'];
        }
        if ($sanitized == true) {
            return substr($this->privateKey, -32);
        } else {
            return $this->privateKey;
        }
    }

    /**
     * Returns full user name as specified in the profile.
     *
     * @return string Full name
     */
    public function getName()
    {
        // Look for value only if not already set
        if (!isset($this->name)) {
            $us = SemanticScuttle_Service_Factory::get('User');
            $user = $us->getUser($this->id);
            $this->name = $user['name'];
        }
        return $this->name;
    }

    /**
     * Returns user email address
     *
     * @return string Email address
     */
    public function getEmail()
    {
        // Look for value only if not already set
        if (!isset($this->email)) {
            $us = SemanticScuttle_Service_Factory::get('User');
            $user = $us->getUser($this->id);
            $this->email = $user['email'];
        }
        return $this->email;
    }

    /**
     * Returns user homepage as specified in the profile.
     *
     * @return string Homepage
     */
    public function getHomepage()
    {
        // Look for value only if not already set
        if(!isset($this->homepage)) {
            $us = SemanticScuttle_Service_Factory::get('User');
            $user = $us->getUser($this->id);
            $this->homepage = $user['homepage'];
        }
        return $this->homepage;
    }

    /**
     * Returns custom user description as specified in the profile.
     *
     * @return string User description
     */
    public function getContent()
    {
        // Look for value only if not already set
        if(!isset($this->content)) {
            $us = SemanticScuttle_Service_Factory::get('User');
            $user = $us->getUser($this->id);
            $this->content = $user['uContent'];
        }
        return $this->content;
    }

    /**
     * Returns user creation time.
     * UTC/Zulu time zone is used.
     *
     * @return string Datetime value: "YYYY-MM-DD HH:MM:SS"
     */
    public function getDatetime()
    {
        // Look for value only if not already set
        if(!isset($this->content)) {
            $us = SemanticScuttle_Service_Factory::get('User');
            $user = $us->getUser($this->id);
            $this->datetime = $user['uDatetime'];
        }
        return $this->datetime;
    }

    /**
     * Tells you if the user is an administrator
     *
     * @return boolean True if the user is admin
     */
    public function isAdmin()
    {
        // Look for value only if not already set
        if(!isset($this->isAdmin)) {
            $us = SemanticScuttle_Service_Factory::get('User');
            $this->isAdmin = $us->isAdmin($this->username);
        }
        return $this->isAdmin;
    }

    /**
     * Returns the number of bookmarks the user owns
     *
     * @param string $range Range of bookmarks:
     *                      'public', 'shared', 'private'
     *                      or 'all'
     *
     * @return integer Number of bookmarks
     *
     * @uses SemanticScuttle_Service_Bookmark::countBookmarks()
     */
    public function getNbBookmarks($range = 'public')
    {
        $bs = SemanticScuttle_Service_Factory::get('Bookmark');
        return $bs->countBookmarks($this->getId(), $range);
    }

}
?>
