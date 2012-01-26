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

require_once 'SemanticScuttle/Model/User.php';

/**
 * SemanticScuttle user management service.
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Benjamin Huynh-Kim-Bang <mensonge@users.sourceforge.net>
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @author   Eric Dane <ericdane@users.sourceforge.net>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */
class SemanticScuttle_Service_User extends SemanticScuttle_DbService
{
    /**
     * The ID of the currently logged on user.
     * NULL when not logged in.
     *
     * @var integer
     */
    protected $currentuserId = null;

    /**
     * Currently logged on user from database
     *
     * @var array
     *
     * @see getCurrentUserId()
     * @see getCurrentUser()
     * @see setCurrentUserId()
     */
    protected $currentuser = null;

    protected $fields = array(
        'primary'    => 'uId',
        'username'   => 'username',
        'password'   => 'password',
        'privateKey' => 'privateKey'
    );

    protected $profileurl;
    protected $sessionkey;
    protected $cookiekey;
    protected $cookietime = 1209600; // 2 weeks

    /**
     * Returns the single service instance
     *
     * @param sql_db $db Database object
     *
     * @return SemanticScuttle_Service_User
     */
    public static function getInstance($db)
    {
        static $instance;
        if (!isset($instance)) {
            $instance = new self($db);
        }
        return $instance;
    }

    protected function __construct($db)
    {
        $this->db = $db;
        $this->tablename  = $GLOBALS['tableprefix'] .'users';
        $this->sessionkey = INSTALLATION_ID.'-currentuserid';
        $this->cookiekey  = INSTALLATION_ID.'-login';
        $this->profileurl = createURL('profile', '%2$s');
        $this->updateSessionStability();
    }

    /**
     * Fetches the desired user row from database, specified by column and value
     *
     * @param string $fieldname Name of database column to identify user
     * @param string $value     Value of $fieldname
     *
     * @return array Database row or boolean false
     */
    protected function _getuser($fieldname, $value)
    {
        $query = 'SELECT * FROM '. $this->getTableName()
            . ' WHERE ' . $fieldname . ' = "' . $this->db->sql_escape($value) . '"';

        if (!($dbresult = $this->db->sql_query($query)) ) {
            message_die(
                GENERAL_ERROR, 'Could not get user',
                '', __LINE__, __FILE__, $query, $this->db
            );
            return false;
        }

        $row = $this->db->sql_fetchrow($dbresult);
        $this->db->sql_freeresult($dbresult);
        if ($row) {
            return $row;
        } else {
            return false;
        }
    }

    function & getUsers($nb=0) {
        $query = 'SELECT * FROM '. $this->getTableName() .' ORDER BY `uId` DESC';
        if($nb>0) {
            $query .= ' LIMIT 0, '.$nb;
        }
        if (! ($dbresult = $this->db->sql_query($query)) ) {
            message_die(GENERAL_ERROR, 'Could not get user', '', __LINE__, __FILE__, $query, $this->db);
            return false;
        }

        while ($row = $this->db->sql_fetchrow($dbresult)) {
            $users[] = $row;
        }
        $this->db->sql_freeresult($dbresult);
        return $users;
    }

    /**
     * Returns an array of user objects.
     * Array is in order of uids
     *
     * @param integer $nb Number of users to fetch.
     *
     * @return array Array of SemanticScuttle_Model_User objects
     */
    public function getObjectUsers($nb = 0)
    {
        $query = 'SELECT * FROM ' . $this->getTableName()
            . ' ORDER BY uId DESC';

        if ($nb > 0) {
            $query .= ' LIMIT 0, ' . intval($nb);
        }

        if (! ($dbresult = $this->db->sql_query($query)) ) {
            message_die(
                GENERAL_ERROR, 'Could not get user',
                '', __LINE__, __FILE__, $query, $this->db
            );
            return false;
        }

        $users = array();
        while ($row = $this->db->sql_fetchrow($dbresult)) {
            $users[] = new SemanticScuttle_Model_User(
                $row[$this->getFieldName('primary')],
                $row[$this->getFieldName('username')]
            );
        }
        $this->db->sql_freeresult($dbresult);
        return $users;
    }

    function _randompassword() {
        $seed = (integer) md5(microtime());
        mt_srand($seed);
        $password = mt_rand(1, 99999999);
        $password = substr(md5($password), mt_rand(0, 19), mt_rand(6, 12));
        return $password;
    }

    /**
     * Updates a single field in the user's database row
     *
     * @param integer $uId       ID of the user
     * @param string  $fieldname Name of table column to change
     * @param string  $value     New value
     *
     * @return boolean True if all was well, false if not
     */
    public function _updateuser($uId, $fieldname, $value)
    {
        $updates = array ($fieldname => $value);
        $sql = 'UPDATE '. $this->getTableName()
            . ' SET '. $this->db->sql_build_array('UPDATE', $updates)
            . ' WHERE '. $this->getFieldName('primary') . '=' . intval($uId);

        // Execute the statement.
        $this->db->sql_transaction('begin');
        if (!($dbresult = $this->db->sql_query($sql))) {
            $this->db->sql_transaction('rollback');
            message_die(
                GENERAL_ERROR, 'Could not update user', '',
                __LINE__, __FILE__, $sql, $this->db
            );
            return false;
        }
        $this->db->sql_transaction('commit');

        // Everything worked out, so return true.
        return true;
    }

    function getProfileUrl($id, $username) {
        return sprintf($this->profileurl, urlencode($id), urlencode($username));
    }

    function getUserByUsername($username) {
        return $this->_getuser($this->getFieldName('username'), $username);
    }

    /**
     * Returns user row from database.
     *
     * @param string $privateKey Private Key
     *
     * @return array User array from database, false if no user was found
     */
    public function getUserByPrivateKey($privateKey)
    {
        return $this->_getuser($this->getFieldName('privateKey'), $privateKey);
    }

    function getObjectUserByUsername($username) {
        $user = $this->_getuser($this->getFieldName('username'), $username);
        if($user != false) {
            return new SemanticScuttle_Model_User(
                $user[$this->getFieldName('primary')], $username
            );
        } else {
            return NULL;
        }
    }

    /**
     * Obtains the ID of the given user name.
     * If a user ID is passed, it is returned.
     * In case the user does not exist, NULL is returned.
     *
     * @param string|integer $user User name or user ID
     *
     * @return integer NULL if not found or the user ID
     */
    public function getIdFromUser($user)
    {
        if (is_int($user)) {
            return intval($user);
        } else {
            $objectUser = $this->getObjectUserByUsername($user);
            if ($objectUser != null) {
                return $objectUser->getId();
            }
        }
        return null;
    }

    /**
     * Returns user row from database.
     *
     * @param integer $id User ID
     *
     * @return array User array from database
     */
    public function getUser($id)
    {
        return $this->_getuser($this->getFieldName('primary'), $id);
    }

    /**
     * Returns user object for given user id
     *
     * @param integer $id User ID
     *
     * @return SemanticScuttle_Model_User User object
     */
    public function getObjectUser($id)
    {
        $user = $this->_getuser($this->getFieldName('primary'), $id);
        return new SemanticScuttle_Model_User(
            $id, $user[$this->getFieldName('username')]
        );
    }

    function isLoggedOn() {
        return ($this->getCurrentUserId() !== false);
    }

    /**
     * Tells you if the private key is enabled and valid
     *
     * @param string $privateKey Private Key
     *
     * @return boolean True if enabled and valid
     */
    public function isPrivateKeyValid($privateKey)
    {
        // check length of private key
        if (strlen($privateKey) == 32) {
            return true;
        }
        return false;
    }

    /**
     * Returns the current user object
     *
     * @param boolean $refresh Reload the user from database
     *                         based on current user id
     * @param mixed   $newval  New user value (used internally
     *                         as setter method)
     *
     * @return array User from database
     */
    public function getCurrentUser($refresh = false, $newval = null)
    {
        if (!is_null($newval)) {
            //internal use only: reset currentuser
            $this->currentuser = $newval;
        } else if ($refresh || !isset($this->currentuser)) {
            if ($id = $this->getCurrentUserId()) {
                $this->currentuser = $this->getUser($id);
            } else {
                $this->currentuser = null;
            }
        }
        return $this->currentuser;
    }

    /**
     * Return current user as object
     *
     * @param boolean $refresh Reload the user from database
     *                         based on current user id
     * @param mixed   $newval  New user value (used internally
     *                         as setter method)
     *
     * @return SemanticScuttle_Model_User User object
     */
    function getCurrentObjectUser($refresh = false, $newval = null)
    {
        static $currentObjectUser;
        if (!is_null($newval)) {
            //internal use only: reset currentuser
            $currentObjectUser = $newval;
        } else if ($refresh || !isset($currentObjectUser)) {
            if ($id = $this->getCurrentUserId()) {
                $currentObjectUser = $this->getObjectUser($id);
            } else {
                $currentObjectUser = null;
            }
        }
        return $currentObjectUser;
    }

    function existsUserWithUsername($username) {
        if($this->getUserByUsername($username) != '') {
            return true;
        } else {
            return false;
        }
    }

    function existsUser($id) {
        if($this->getUser($id) != '') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Checks if the given user is an administrator.
     * Uses global admin_users property containing admin
     * user names.
     *
     * Passing the user id makes this function load the user
     * from database. For efficiency reasons, try to pass
     * the user name or database row.
     *
     * @param integer|array|string $user User ID or user row from DB
     *                                   or user name
     *
     * @return boolean True if the user is admin
     */
    function isAdmin($user)
    {
        if (is_numeric($user)) {
            $user = $this->getUser($user);
            $user = $user['username'];
        } else if (is_array($user)) {
            $user = $user['username'];
        }

        if (isset($GLOBALS['admin_users'])
            && in_array($user, $GLOBALS['admin_users'])
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Return current user id based on session or cookie
     *
     * @return mixed Integer user id or boolean false when user
     *               could not be found or is not logged on.
     */
    public function getCurrentUserId()
    {
        if ($this->currentuserId !== null) {
            return $this->currentuserId;
        }

        if (isset($_SESSION[$this->getSessionKey()])) {
            $this->currentuserId = (int)$_SESSION[$this->getSessionKey()];
            return $this->currentuserId;

        }

        if (isset($_COOKIE[$this->getCookieKey()])) {
            $cook = explode(':', $_COOKIE[$this->getCookieKey()]);
            //cookie looks like this: 'id:md5(username+password)'
            $query = 'SELECT * FROM '. $this->getTableName() .
                     ' WHERE MD5(CONCAT('.$this->getFieldName('username') .
                                     ', '.$this->getFieldName('password') .
                     ')) = \''.$this->db->sql_escape($cook[1]).'\' AND '.
            $this->getFieldName('primary'). ' = '. $this->db->sql_escape($cook[0]);

            if (! ($dbresult = $this->db->sql_query($query)) ) {
                message_die(
                    GENERAL_ERROR, 'Could not get user',
                    '', __LINE__, __FILE__, $query, $this->db
                );
                return false;
            }

            if ($row = $this->db->sql_fetchrow($dbresult)) {
                $this->setCurrentUserId(
                    (int)$row[$this->getFieldName('primary')], true
                );
                $this->db->sql_freeresult($dbresult);
                return $this->currentuserId;
            }
        }

        $ssls = SemanticScuttle_Service_Factory::get('User_SslClientCert');
        if ($ssls->hasValidCert()) {
            $id = $ssls->getUserIdFromCert();
            if ($id !== false) {
                $this->setCurrentUserId($id, true);
                return $this->currentuserId;
            }
        }
        return false;
    }



    /**
     * Set the current user ID (i.e. when logging on)
     *
     * @internal
     * No ID verification is being done.
     *
     * @param integer $user           User ID or null to unset the user
     * @param boolean $storeInSession Store the user ID in the session
     *
     * @return void
     */
    public function setCurrentUserId($user, $storeInSession = false)
    {
        if ($user === null) {
            $this->currentuserId = null;
            if ($storeInSession) {
                unset($_SESSION[$this->getSessionKey()]);
            }
        } else {
            $this->currentuserId = (int)$user;
            if ($storeInSession) {
                $_SESSION[$this->getSessionKey()] = $this->currentuserId;
            }
        }
        //reload user object
        $this->getCurrentUser(true);
        $this->getCurrentObjectUser(true);
    }



    /**
     * Try to authenticate and login a user with
     * username and password.
     *
     * @param string  $username Name of user
     * @param string  $password Password
     * @param boolean $remember If a long-time cookie shall be set
     *
     * @return boolean True if the user could be authenticated,
     *                 false if not.
     */
    public function login($username, $password, $remember = false)
    {
        $password = $this->sanitisePassword($password);
        $query = 'SELECT '. $this->getFieldName('primary') .' FROM '. $this->getTableName() .' WHERE '. $this->getFieldName('username') .' = "'. $this->db->sql_escape($username) .'" AND '. $this->getFieldName('password') .' = "'. $this->db->sql_escape($password) .'"';

        if (!($dbresult = $this->db->sql_query($query))) {
            message_die(
                GENERAL_ERROR,
                'Could not get user',
                '', __LINE__, __FILE__, $query, $this->db
            );
            return false;
        }

        $row = $this->db->sql_fetchrow($dbresult);
        $this->db->sql_freeresult($dbresult);

        if ($row) {
            $this->setCurrentUserId($row[$this->getFieldName('primary')], true);
            if ($remember) {
                $cookie = $this->currentuserId . ':' . md5($username.$password);
                setcookie(
                    $this->cookiekey, $cookie,
                    time() + $this->cookietime, '/'
                );
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * Try to authenticate via the privateKey
     *
     * @param string $privateKey Private Key
     *
     * @return boolean true if the user could be authenticated,
     *                 false if not.
     */
    public function loginPrivateKey($privateKey)
    {
        /* Check if private key valid and enabled */
        if (!$this->isPrivateKeyValid($privateKey)) {
            return false;
        }

        $query = 'SELECT '. $this->getFieldName('primary') .' FROM '
            . $this->getTableName() .' WHERE '
            . $this->getFieldName('privateKey') .' = "'
            . $this->db->sql_escape($privateKey) .'"';

        if (!($dbresult = $this->db->sql_query($query))) {
            message_die(
                GENERAL_ERROR,
                'Could not get user',
                '', __LINE__, __FILE__, $query, $this->db
            );
            return false;
        }

        $row = $this->db->sql_fetchrow($dbresult);
        $this->db->sql_freeresult($dbresult);

        if ($row) {
            $this->setCurrentUserId($row[$this->getFieldName('primary')], false);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Logs the user off
     *
     * @return void
     */
    public function logout()
    {
        @setcookie($this->getCookiekey(), '', time() - 1, '/');
        unset($_COOKIE[$this->getCookiekey()]);
        session_unset();
        $this->currentuserId = null;
        $this->currentuser = null;
    }

    function getWatchlist($uId) {
        // Gets the list of user IDs being watched by the given user.
        $query = 'SELECT watched FROM '. $GLOBALS['tableprefix'] .'watched WHERE uId = '. intval($uId);

        if (! ($dbresult = $this->db->sql_query($query)) ) {
            message_die(GENERAL_ERROR, 'Could not get watchlist', '', __LINE__, __FILE__, $query, $this->db);
            return false;
        }

        $arrWatch = array();
        if ($this->db->sql_numrows($dbresult) == 0) {
            $this->db->sql_freeresult($dbresult);
            return $arrWatch;
        }
        while ($row = $this->db->sql_fetchrow($dbresult)) {
            $arrWatch[] = $row['watched'];
        }
        $this->db->sql_freeresult($dbresult);
        return $arrWatch;
    }


    /**
     * Gets the list of user names being watched by the given user.
     *
     * @param integer $uId       User ID
     * @param boolean $watchedby if false: get the list of users that $uId watches
     *                           if true: get the list of users that watch $uId
     *
     * @return array Array of user names
     */
    public function getWatchNames($uId, $watchedby = false)
    {
        if ($watchedby) {
            $table1 = 'b';
            $table2 = 'a';
        } else {
            $table1 = 'a';
            $table2 = 'b';
        }
        $primary   = $this->getFieldName('primary');
        $userfield = $this->getFieldName('username');
        $query = 'SELECT '. $table1 .'.'. $userfield
            . ' FROM '. $GLOBALS['tableprefix'] . 'watched AS W,'
            . ' ' . $this->getTableName() .' AS a,'
            . ' ' . $this->getTableName() .' AS b'
            . ' WHERE W.watched = a.' . $primary
            . ' AND W.uId = b.' . $primary
            . ' AND ' . $table2 . '.' . $primary . ' = '. intval($uId)
            . ' ORDER BY '. $table1 . '.' . $userfield;

        if (!($dbresult = $this->db->sql_query($query))) {
            message_die(
                GENERAL_ERROR, 'Could not get watchlist',
                '', __LINE__, __FILE__, $query, $this->db
            );
            return false;
        }

        $arrWatch = array();
        if ($this->db->sql_numrows($dbresult) == 0) {
            $this->db->sql_freeresult($dbresult);
            return $arrWatch;
        }
        while ($row = $this->db->sql_fetchrow($dbresult)) {
            $arrWatch[] = $row[$this->getFieldName('username')];
        }
        $this->db->sql_freeresult($dbresult);
        return $arrWatch;
    }


    function getWatchStatus($watcheduser, $currentuser) {
        // Returns true if the current user is watching the given user, and false otherwise.
        $query = 'SELECT watched FROM '. $GLOBALS['tableprefix'] .'watched AS W INNER JOIN '. $this->getTableName() .' AS U ON U.'. $this->getFieldName('primary') .' = W.watched WHERE U.'. $this->getFieldName('primary') .' = '. intval($watcheduser) .' AND W.uId = '. intval($currentuser);

        if (! ($dbresult = $this->db->sql_query($query)) ) {
            message_die(GENERAL_ERROR, 'Could not get watchstatus', '', __LINE__, __FILE__, $query, $this->db);
            return false;
        }

        $retval = true;
        if ($this->db->sql_numrows($dbresult) == 0)
        $retval = false;

        $this->db->sql_freeresult($dbresult);
        return $retval;
    }

    function setWatchStatus($subjectUserID) {
        if (!is_numeric($subjectUserID))
        return false;

        $currentUserID = $this->getCurrentUserId();
        $watched = $this->getWatchStatus($subjectUserID, $currentUserID);

        if ($watched) {
            $sql = 'DELETE FROM '. $GLOBALS['tableprefix'] .'watched WHERE uId = '. intval($currentUserID) .' AND watched = '. intval($subjectUserID);
            if (!($dbresult = $this->db->sql_query($sql))) {
                $this->db->sql_transaction('rollback');
                message_die(GENERAL_ERROR, 'Could not add user to watch list', '', __LINE__, __FILE__, $sql, $this->db);
                return false;
            }
        } else {
            $values = array(
                'uId' => intval($currentUserID),
                'watched' => intval($subjectUserID)
            );
            $sql = 'INSERT INTO '. $GLOBALS['tableprefix'] .'watched '. $this->db->sql_build_array('INSERT', $values);
            if (!($dbresult = $this->db->sql_query($sql))) {
                $this->db->sql_transaction('rollback');
                message_die(GENERAL_ERROR, 'Could not add user to watch list', '', __LINE__, __FILE__, $sql, $this->db);
                return false;
            }
        }

        $this->db->sql_transaction('commit');
        return true;
    }

    /**
     * Create a new user in database.
     * No checks are done in here - you ought to have checked
     * everything before calling this method!
     *
     * @param string $username   Username to use
     * @param string $password   Password to use
     * @param string $email      Email to use
     * @param string $privateKey Key for RSS auth
     *
     * @return mixed Integer user ID if all is well,
     *               boolean false if an error occured
     */
    public function addUser($username, $password, $email, $privateKey = null)
    {
        // Set up the SQL UPDATE statement.
        $datetime = gmdate('Y-m-d H:i:s', time());
        $password = $this->sanitisePassword($password);
        $values   = array(
            'username'   => $username,
            'password'   => $password,
            'email'      => $email,
            'uDatetime'  => $datetime,
            'uModified'  => $datetime,
            'privateKey' => $privateKey
        );
        $sql = 'INSERT INTO '. $this->getTableName()
            . ' '. $this->db->sql_build_array('INSERT', $values);

        // Execute the statement.
        $this->db->sql_transaction('begin');
        if (!($dbresult = $this->db->sql_query($sql))) {
            $this->db->sql_transaction('rollback');
            message_die(
                GENERAL_ERROR, 'Could not insert user',
                '', __LINE__, __FILE__, $sql, $this->db
            );
            return false;
        }
        $uId = $this->db->sql_nextid($dbresult);
        $this->db->sql_transaction('commit');

        return $uId;
    }

    /**
     * Updates the given user
     *
     * @param integer $uId              ID of user to change
     * @param string  $password         Password to use
     * @param string  $name             Realname to use
     * @param string  $email            Email to use
     * @param string  $homepage         User's homepage
     * @param string  $uContent         User note
     * @param string  $privateKey       RSS Private Key
     * @param boolean $enablePrivateKey RSS Private Key Flag
     *
     * @return boolean True when all is well, false if not
     */
    public function updateUser(
        $uId, $password, $name, $email, $homepage, $uContent,
        $privateKey = null, $enablePrivateKey = false
    ) {
        if (!is_numeric($uId)) {
            return false;
        }

        // prepend '-' to privateKey if disabled
        if ($privateKey != null && strlen($privateKey) == 32
            && $enablePrivateKey == false
        ) {
            $privateKey = '-' . $privateKey;
        }

        // remove '-' from privateKey if enabling
        if ($privateKey != null && strlen($privateKey) == 33
            && $enablePrivateKey == true
        ) {
            $privateKey = substr($privateKey, 1, 32);
        }

        // if new user is enabling Private Key, create new key
        if ($privateKey == null && $enablePrivateKey == true) {
            $privateKey = $this->getNewPrivateKey();
        }

        // Set up the SQL UPDATE statement.
        $moddatetime = gmdate('Y-m-d H:i:s', time());
        if ($password == '') {
            $updates = array(
                'uModified'  => $moddatetime,
                'name'       => $name,
                'email'      => $email,
                'homepage'   => $homepage,
                'uContent'   => $uContent,
                'privateKey' => $privateKey
            );
        } else {
            $updates = array(
                'uModified'  => $moddatetime,
                'password'   => $this->sanitisePassword($password),
                'name'       => $name,
                'email'      => $email,
                'homepage'   => $homepage,
                'uContent'   => $uContent,
                'privateKey' => $privateKey
            );
        }
        $sql = 'UPDATE '. $this->getTableName()
            . ' SET '. $this->db->sql_build_array('UPDATE', $updates)
            . ' WHERE '. $this->getFieldName('primary') . '=' . intval($uId);

        // Execute the statement.
        $this->db->sql_transaction('begin');
        if (!($dbresult = $this->db->sql_query($sql))) {
            $this->db->sql_transaction('rollback');
            message_die(
                GENERAL_ERROR, 'Could not update user', '',
                __LINE__, __FILE__, $sql, $this->db
            );
            return false;
        }
        $this->db->sql_transaction('commit');

        // Everything worked out, so return true.
        return true;
    }



    function getAllUsers ( ) {
        $query = 'SELECT * FROM '. $this->getTableName();

        if (! ($dbresult = $this->db->sql_query($query)) ) {
            message_die(GENERAL_ERROR, 'Could not get users', '', __LINE__, __FILE__, $query, $this->db);
            return false;
        }

        $rows = array();

        while ( $row = $this->db->sql_fetchrow($dbresult) ) {
            $rows[] = $row;
        }
        $this->db->sql_freeresult($dbresult);
        return $rows;
    }

    // Returns an array with admin uIds
    function getAdminIds() {
        $admins = array();
        foreach($GLOBALS['admin_users'] as $adminName) {
            if($this->getIdFromUser($adminName) != NULL)
            $admins[] = $this->getIdFromUser($adminName);
        }
        return $admins;
    }

    function deleteUser($uId) {
        $query = 'DELETE FROM '. $this->getTableName() .' WHERE uId = '. intval($uId);

        if (!($dbresult = $this->db->sql_query($query))) {
            message_die(GENERAL_ERROR, 'Could not delete user', '', __LINE__, __FILE__, $query, $this->db);
            return false;
        }

        return true;
    }

    /**
     * Delete all users and their watch states.
     * Mainly used in unit tests.
     *
     * @return void
     */
    public function deleteAll()
    {
        $query = 'TRUNCATE TABLE `'. $this->getTableName() .'`';
        $this->db->sql_query($query);

        $query = 'TRUNCATE TABLE `' . $GLOBALS['tableprefix'] . 'watched' . '`';
        $this->db->sql_query($query);
    }

    /**
     * Hashes the password for storage in/querying the database.
     *
     * @param string $password Password to hash
     *
     * @return string Hashed password
     */
    public function sanitisePassword($password)
    {
        return sha1(trim($password));
    }

    /**
     * Changes the password for the given user to a new, random one.
     *
     * @param integer $uId User ID
     *
     * @return string New password of false if something went wrong
     */
    public function generatePassword($uId)
    {
        if (!is_numeric($uId)) {
            return false;
        }

        $password = $this->_randompassword();

        $ok = $this->_updateuser(
            $uId, $this->getFieldName('password'),
            $this->sanitisePassword($password)
        );

        if ($ok) {
            return $password;
        } else {
            return false;
        }
    }

    /**
     * Generates a new private key and confirms it isn't being used.
     * Private key is 32 characters long, consisting of lowercase and
     * numeric characters.
     *
     * @return string the new key value
     */
    public function getNewPrivateKey()
    {
        do {
            $newKey = md5(uniqid('SemanticScuttle', true));
        } while ($this->privateKeyExists($newKey));

        return $newKey;
    }

    /**
     * Checks if a private key already exists
     *
     * @param string $privateKey key that has been generated
     *
     * @return boolean true when the private key exists,
     *                 False if not.
     */
    public function privateKeyExists($privateKey)
    {
        if (!$privateKey) {
            return false;
        }
        $crit = array('privateKey' => $privateKey);

        $sql = 'SELECT COUNT(*) as "0" FROM '
            . $GLOBALS['tableprefix'] . 'users'
            . ' WHERE '. $this->db->sql_build_array('SELECT', $crit);

        if (!($dbresult = $this->db->sql_query($sql))) {
            message_die(
                GENERAL_ERROR, 'Could not get vars', '',
                __LINE__, __FILE__, $sql, $this->db
            );
        }
        if ($this->db->sql_fetchfield(0, 0) > 0) {
            $exists = true;
        } else {
            $exists = false;
        }
        $this->db->sql_freeresult($dbresult);
        return $exists;
    }

    function isReserved($username) {
        if (in_array($username, $GLOBALS['reservedusers'])) {
            return true;
        } else {
            return false;
        }
    }

    function isValidUsername($username) {
        if (strlen($username) < 4) {
            return false;
        }elseif (strlen($username) > 24) {
            // too long usernames are cut by database and may cause bugs when compared
            return false;
        } elseif (preg_match('/(\W)/', $username) > 0) {
            // forbidden non-alphanumeric characters
            return false;
        }
        return true;
    }



    /**
     * Checks if the given email address is valid
     *
     * @param string $email Email address
     *
     * @return boolean True if it is valid, false if not
     */
    public function isValidEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Sets a session variable.
     * Updates it when it is already set.
     * This is used to detect if cookies work.
     *
     * @return void
     *
     * @see isSessionStable()
     */
    public function updateSessionStability()
    {
        //find out if we have cookies enabled
        if (!isset($_SESSION['sessionStable'])) {
            $_SESSION['sessionStable'] = 0;
        } else {
            $_SESSION['sessionStable'] = 1;
        }
    }

    /**
     * Tells you if the session is fresh or old.
     * If the session is fresh, it's the first page
     * call with that session id. If the session is old,
     * we know that cookies (or session persistance) works
     *
     * @return boolean True if the
     *
     * @see updateSessionStability()
     */
    public function isSessionStable()
    {
        return $_SESSION['sessionStable'] == 1;
    }

    /**
     * Get database column name.
     *
     * @param string $field Field name like 'primary', 'username'
     *                      and 'password'
     *
     * @return string Real field name
     */
    public function getFieldName($field)
    {
        return $this->fields[$field];
    }

    /**
     * Set field name
     *
     * @param string $field Field name like 'primary', 'username'
     *                      and 'password'
     * @param string $value Real database column name
     *
     * @return void
     */
    public function setFieldName($field, $value)
    {
        $this->fields[$field] = $value;
    }

    function getSessionKey()       { return $this->sessionkey; }
    function setSessionKey($value) { $this->sessionkey = $value; }

    function getCookieKey()       { return $this->cookiekey; }
    function setCookieKey($value) { $this->cookiekey = $value; }
}

?>
