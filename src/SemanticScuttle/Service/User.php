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
        'primary'   =>  'uId',
        'username'  =>  'username',
        'password'  =>  'password'
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
        if (! ($dbresult =& $this->db->sql_query($query)) ) {
            message_die(GENERAL_ERROR, 'Could not get user', '', __LINE__, __FILE__, $query, $this->db);
            return false;
        }

        while ($row = & $this->db->sql_fetchrow($dbresult)) {
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

        if (! ($dbresult =& $this->db->sql_query($query)) ) {
            message_die(
                GENERAL_ERROR, 'Could not get user',
                '', __LINE__, __FILE__, $query, $this->db
            );
            return false;
        }

        $users = array();
        while ($row = & $this->db->sql_fetchrow($dbresult)) {
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

    function _updateuser($uId, $fieldname, $value) {
        $updates = array ($fieldname => $value);
        $sql = 'UPDATE '. $this->getTableName() .' SET '. $this->db->sql_build_array('UPDATE', $updates) .' WHERE '. $this->getFieldName('primary') .'='. intval($uId);

        // Execute the statement.
        $this->db->sql_transaction('begin');
        if (!($dbresult = & $this->db->sql_query($sql))) {
            $this->db->sql_transaction('rollback');
            message_die(GENERAL_ERROR, 'Could not update user', '', __LINE__, __FILE__, $sql, $this->db);
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

    /* Takes an numerical "id" or a string "username"
     and returns the numerical "id" if the user exists else returns NULL */
    function getIdFromUser($user) {
        if (is_int($user)) {
            return intval($user);
        } else {
            $objectUser = $this->getObjectUserByUsername($user);
            if($objectUser != NULL) {
                return $objectUser->getId();
            }
        }
        return NULL;
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
            $currentuser = $newval;
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
        if (isset($_SESSION[$this->getSessionKey()])) {
            return (int)$_SESSION[$this->getSessionKey()];

        } else if (isset($_COOKIE[$this->getCookieKey()])) {
            $cook = explode(':', $_COOKIE[$this->getCookieKey()]);
            //cookie looks like this: 'id:md5(username+password)'
            $query = 'SELECT * FROM '. $this->getTableName() .
                     ' WHERE MD5(CONCAT('.$this->getFieldName('username') .
                                     ', '.$this->getFieldName('password') .
                     ')) = \''.$this->db->sql_escape($cook[1]).'\' AND '.
            $this->getFieldName('primary'). ' = '. $this->db->sql_escape($cook[0]);

            if (! ($dbresult =& $this->db->sql_query($query)) ) {
                message_die(
                    GENERAL_ERROR, 'Could not get user',
                    '', __LINE__, __FILE__, $query, $this->db
                );
                return false;
            }

            if ($row = $this->db->sql_fetchrow($dbresult)) {
                $this->setCurrentUserId(
                    (int)$row[$this->getFieldName('primary')]
                );
                $this->db->sql_freeresult($dbresult);
                return (int)$_SESSION[$this->getSessionKey()];
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
     * @param integer $user User ID or null to unset the user
     *
     * @return void
     */
    public function setCurrentUserId($user)
    {
        if ($user === null) {
            unset($_SESSION[$this->getSessionKey()]);
        } else {
            $_SESSION[$this->getSessionKey()] = (int)$user;
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
            $id = $_SESSION[$this->getSessionKey()]
                = $row[$this->getFieldName('primary')];
            if ($remember) {
                $cookie = $id .':'. md5($username.$password);
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

    function logout() {
        @setcookie($this->getCookiekey(), '', time() - 1, '/');
        unset($_COOKIE[$this->getCookiekey()]);
        session_unset();
        $this->getCurrentUser(TRUE, false);
    }

    function getWatchlist($uId) {
        // Gets the list of user IDs being watched by the given user.
        $query = 'SELECT watched FROM '. $GLOBALS['tableprefix'] .'watched WHERE uId = '. intval($uId);

        if (! ($dbresult =& $this->db->sql_query($query)) ) {
            message_die(GENERAL_ERROR, 'Could not get watchlist', '', __LINE__, __FILE__, $query, $this->db);
            return false;
        }

        $arrWatch = array();
        if ($this->db->sql_numrows($dbresult) == 0) {
            $this->db->sql_freeresult($dbresult);
            return $arrWatch;
        }
        while ($row =& $this->db->sql_fetchrow($dbresult)) {
            $arrWatch[] = $row['watched'];
        }
        $this->db->sql_freeresult($dbresult);
        return $arrWatch;
    }

    function getWatchNames($uId, $watchedby = false) {
        // Gets the list of user names being watched by the given user.
        // - If $watchedby is false get the list of users that $uId watches
        // - If $watchedby is true get the list of users that watch $uId
        if ($watchedby) {
            $table1 = 'b';
            $table2 = 'a';
        } else {
            $table1 = 'a';
            $table2 = 'b';
        }
        $query = 'SELECT '. $table1 .'.'. $this->getFieldName('username') .' FROM '. $GLOBALS['tableprefix'] .'watched AS W, '. $this->getTableName() .' AS a, '. $this->getTableName() .' AS b WHERE W.watched = a.'. $this->getFieldName('primary') .' AND W.uId = b.'. $this->getFieldName('primary') .' AND '. $table2 .'.'. $this->getFieldName('primary') .' = '. intval($uId) .' ORDER BY '. $table1 .'.'. $this->getFieldName('username');

        if (!($dbresult =& $this->db->sql_query($query))) {
            message_die(GENERAL_ERROR, 'Could not get watchlist', '', __LINE__, __FILE__, $query, $this->db);
            return false;
        }

        $arrWatch = array();
        if ($this->db->sql_numrows($dbresult) == 0) {
            $this->db->sql_freeresult($dbresult);
            return $arrWatch;
        }
        while ($row =& $this->db->sql_fetchrow($dbresult)) {
            $arrWatch[] = $row[$this->getFieldName('username')];
        }
        $this->db->sql_freeresult($dbresult);
        return $arrWatch;
    }

    function getWatchStatus($watcheduser, $currentuser) {
        // Returns true if the current user is watching the given user, and false otherwise.
        $query = 'SELECT watched FROM '. $GLOBALS['tableprefix'] .'watched AS W INNER JOIN '. $this->getTableName() .' AS U ON U.'. $this->getFieldName('primary') .' = W.watched WHERE U.'. $this->getFieldName('primary') .' = '. intval($watcheduser) .' AND W.uId = '. intval($currentuser);

        if (! ($dbresult =& $this->db->sql_query($query)) ) {
            message_die(GENERAL_ERROR, 'Could not get watchstatus', '', __LINE__, __FILE__, $query, $this->db);
            return false;
        }

        $arrWatch = array();
        if ($this->db->sql_numrows($dbresult) == 0)
        return false;
        else
        return true;
    }

    function setWatchStatus($subjectUserID) {
        if (!is_numeric($subjectUserID))
        return false;

        $currentUserID = $this->getCurrentUserId();
        $watched = $this->getWatchStatus($subjectUserID, $currentUserID);

        if ($watched) {
            $sql = 'DELETE FROM '. $GLOBALS['tableprefix'] .'watched WHERE uId = '. intval($currentUserID) .' AND watched = '. intval($subjectUserID);
            if (!($dbresult =& $this->db->sql_query($sql))) {
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
            if (!($dbresult =& $this->db->sql_query($sql))) {
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
     * @param string $username Username to use
     * @param string $password Password to use
     * @param string $email    Email to use
     *
     * @return mixed Integer user ID if all is well,
     *               boolean false if an error occured
     */
    public function addUser($username, $password, $email)
    {
        // Set up the SQL UPDATE statement.
        $datetime = gmdate('Y-m-d H:i:s', time());
        $password = $this->sanitisePassword($password);
        $values   = array(
            'username'  => $username,
            'password'  => $password,
            'email'     => $email,
            'uDatetime' => $datetime,
            'uModified' => $datetime
        );
        $sql = 'INSERT INTO '. $this->getTableName()
            . ' '. $this->db->sql_build_array('INSERT', $values);

        // Execute the statement.
        $this->db->sql_transaction('begin');
        if (!($dbresult = & $this->db->sql_query($sql))) {
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

    function updateUser($uId, $password, $name, $email, $homepage, $uContent) {
        if (!is_numeric($uId))
        return false;

        // Set up the SQL UPDATE statement.
        $moddatetime = gmdate('Y-m-d H:i:s', time());
        if ($password == '')
        $updates = array ('uModified' => $moddatetime, 'name' => $name, 'email' => $email, 'homepage' => $homepage, 'uContent' => $uContent);
        else
        $updates = array ('uModified' => $moddatetime, 'password' => $this->sanitisePassword($password), 'name' => $name, 'email' => $email, 'homepage' => $homepage, 'uContent' => $uContent);
        $sql = 'UPDATE '. $this->getTableName() .' SET '. $this->db->sql_build_array('UPDATE', $updates) .' WHERE '. $this->getFieldName('primary') .'='. intval($uId);

        // Execute the statement.
        $this->db->sql_transaction('begin');
        if (!($dbresult = & $this->db->sql_query($sql))) {
            $this->db->sql_transaction('rollback');
            message_die(GENERAL_ERROR, 'Could not update user', '', __LINE__, __FILE__, $sql, $this->db);
            return false;
        }
        $this->db->sql_transaction('commit');

        // Everything worked out, so return true.
        return true;
    }

    function getAllUsers ( ) {
        $query = 'SELECT * FROM '. $this->getTableName();

        if (! ($dbresult =& $this->db->sql_query($query)) ) {
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

        if (!($dbresult = & $this->db->sql_query($query))) {
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
