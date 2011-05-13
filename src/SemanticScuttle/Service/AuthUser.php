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

require_once 'Auth.php';
require_once 'SemanticScuttle/Service/User.php';

/**
 * SemanticScuttle extendet user management service utilizing
 * the PEAR Auth package to enable authentication against
 * different services, i.e. LDAP or other databases.
 *
 * Requires the Log packages for debugging purposes.
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */
class SemanticScuttle_Service_AuthUser extends SemanticScuttle_Service_User
{
    /**
     * PEAR Auth instance
     *
     * @var Auth
     */
    protected $auth = null;

    /**
     * If we want to debug authentication process
     *
     * @var boolean
     */
    protected $authdebug = false;

    /**
    * Authentication type (i.e. LDAP)
    *
    * @var string
    *
    * @link http://pear.php.net/manual/en/package.authentication.auth.intro-storage.php
    */
    var $authtype = null;
    
    /**
    * Authentication options
    *
    * @var array
    *
    * @link http://pear.php.net/manual/en/package.authentication.auth.intro.php
    */
    var $authoptions = null;



    /**
     * Returns the single service instance
     *
     * @param sql_db $db Database object
     *
     * @return SemanticScuttle_Service_AuthUser
     */
    public static function getInstance($db)
    {
        static $instance;
        if (!isset($instance)) {
            $instance = new self($db);
        }
        return $instance;
    }



    /**
     * Create new instance
     *
     * @var sql_db $db Database object
     */
    protected function __construct($db)
    {
        parent::__construct($db);

        $this->authtype    = $GLOBALS['authType'];
        $this->authoptions = $GLOBALS['authOptions'];
        $this->authdebug   = $GLOBALS['authDebug'];

        //FIXME: throw error when no authtype set?
        if (!$this->authtype) {
            return;
        }
        require_once 'Auth.php';
        $this->auth = new Auth($this->authtype, $this->authoptions);
        //FIXME: check if it worked (i.e. db connection)
        if ($this->authdebug) {
            require_once 'Log.php';
            $this->auth->logger = Log::singleton(
                'display', '', '', array(), PEAR_LOG_DEBUG
            );
            $this->auth->enableLogging = true;
        }
        $this->auth->setShowLogin(false);
    }



    /**
     * Return current user id based on session or cookie
     *
     * @return mixed Integer user id or boolean false when user
     *               could not be found or is not logged on.
     */
    public function getCurrentUserId()
    {
        if (!$this->auth) {
            return parent::getCurrentUserId();
        }

        //FIXME: caching?
        $name = $this->auth->getUsername();
        if (!$name) {
            return parent::getCurrentUserId();
        }
        return $this->getIdFromUser($name);
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
        if (!$this->auth) {
            return parent::login($username, $password, $remember);
        }

        $ok = $this->loginAuth($username, $password);
        if (!$ok) {
            return false;
        }

        //utilize real login method to get longtime cookie support etc.
        $ok = parent::login($username, $password, $remember);
        if ($ok) {
            return $ok;
        }

        //user must have changed password in external auth.
        //we need to update the local database.
        $user = $this->getUserByUsername($username);
        $this->_updateuser(
            $user['uId'], $this->getFieldName('password'),
            $this->sanitisePassword($password)
        );

        return parent::login($username, $password, $remember);
    }


    /**
    * Uses PEAR's Auth class to authenticate the user against a container.
    * This allows us to use LDAP, a different database or some other
    * external system.
    *
    * @param string $username Username to check
    * @param string $password Password to check
    *
    * @return boolean If the user has been successfully authenticated or not
    */
    public function loginAuth($username, $password)
    {
        $this->auth->post = array(
            'username' => $username,
            'password' => $password,
        );
        $this->auth->start();

        if (!$this->auth->checkAuth()) {
            return false;
        }

        //put user in database
        if (!$this->getUserByUsername($username)) {
            $this->addUser(
                $username, $password,
                $username . $GLOBALS['authEmailSuffix']
            );
        }

        return true;
     }




    /**
     * Logs the current user out of the system.
     *
     * @return void
     */
    public function logout()
    {
        parent::logout();

        if ($this->auth) {
            $this->auth->logout();
            $this->auth = null;
        }
    }

}
?>