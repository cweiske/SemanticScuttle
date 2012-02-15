<?php
/**
 * SemanticScuttle - your social bookmark manager.
 *
 * PHP version 5.
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  AGPL http://www.gnu.org/licenses/agpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */

require_once 'SemanticScuttle/Exception/User.php';
require_once 'SemanticScuttle/Model/OpenId.php';
require_once 'OpenID.php';
require_once 'OpenID/RelyingParty.php';
require_once 'OpenID/Extension/SREG11.php';

/**
 * SemanticScuttle OpenID verification and management
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  AGPL http://www.gnu.org/licenses/agpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */
class SemanticScuttle_Service_OpenId extends SemanticScuttle_DbService
{
    /**
     * Creates a new instance, sets database variable and table name.
     *
     * @param sql_db $db Database object
     */
    protected function __construct($db)
    {
        $this->db = $db;
        $this->tablename  = $GLOBALS['tableprefix'] . 'users_openids';
    }

    /**
     * Returns the single service instance
     *
     * @param sql_db $db Database object
     *
     * @return SemanticScuttle_Service_OpenId
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
     * When the user gives an e-mail address instead of an OpenID, we use
     * WebFinger to find his OpenID.
     *
     * @param string $identifier OpenID URL OR e-mail address
     *
     * @return string Raw/unnormalized OpenID URL.
     *
     * @throws SemanticScuttle_Exception_User When the user's mail host does not
     *                                        support WebFinger
     */
    protected function resolveEmailIdentifier($identifier)
    {
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL) === false) {
            //no valid email
            return $identifier;
        }

        require_once 'Net/WebFinger.php';
        require_once 'HTTP/Request2.php';

        $req = new HTTP_Request2();
        $req->setConfig('follow_redirects', true);
        $req->setHeader('User-Agent', 'SemanticScuttle');

        $wf  = new Net_WebFinger();
        $wf->setHttpClient($req);
        $react = $wf->finger($identifier);
        if ($react->openid === null) {
            throw new SemanticScuttle_Exception_User(
                'No OpenID found for the given email address ' . $identifier,
                20
            );
        }

        return $react->openid;
    }

    /**
     * Part 1 of the OpenID login process: Send user to his identity provider.
     *
     * If an e-mail address is given, a WebFinger lookup is made to find out the
     * user's OpenID.
     *
     * This method exits the PHP process.
     *
     * @param string $identifier OpenID URL OR e-mail address
     * @param string $returnUrl  URL the identity provider shall send the user
     *                           back to
     *
     * @return void No return value needed since it exits.
     *
     * @throws SemanticScuttle_Exception_User When something goes wrong
     */
    public function sendIdRequest($identifier, $returnUrl)
    {
        $identifier = $this->resolveEmailIdentifier($identifier);

        //send request to ID provider
        try {
            $identifier = OpenID::normalizeIdentifier($identifier);
        } catch (OpenID_Exception $e) {
            throw new SemanticScuttle_Exception_User(
                'Invalid OpenID identifier', 11, $e
            );
        }

        try {
            $rp = new OpenID_RelyingParty(
                $returnUrl,
                addProtocolToUrl(ROOT)/* realm */,
                $identifier
            );
            $authRequest = $rp->prepare();

            //FIXME: when user exists already, use immediate mode first and
            // fall back to normal when it fails
            //FIXME: (?) when user exists already, don't request details

            $sreg = new OpenID_Extension_SREG11(OpenID_Extension::REQUEST);
            //$sreg->set('required', 'email');
            $sreg->set('optional', 'email,nickname,fullname');
            $authRequest->addExtension($sreg);
            //$auth->setMode(OpenID::MODE_CHECKID_IMMEDIATE);
            header('Location: ' . $authRequest->getAuthorizeURL());
            exit();
        } catch (OpenID_Exception $e) {
            throw new SemanticScuttle_Exception_User(
                'Error communicating with OpenID identity server', 12, $e
            );
        }
    }

    /**
     * Part 2 of the OpenID login process: Handle the IDP response
     *
     * @param string $returnUrl  URL the identity provider shall send the user
     *                           back to
     *
     * @return array Array with user data. Keys:
     *               - identifier - OpenID URL/identifier
     *               - userId     - Local user ID from database
     *               - email      - OpenID-submitted email
     *               - nickname   - OpenID-submitted nickname
     *               - fullname   - OpenID-submitted full name
     *
     * @throws SemanticScuttle_Exception_User When something goes wrong
     */
    public function handleIdResponse($returnUrl)
    {
        $rp = new OpenID_RelyingParty(
            $returnUrl,
            addProtocolToUrl(ROOT)/* realm */
        );

        if (!count($_POST)) {
            list(, $queryString) = explode('?', $_SERVER['REQUEST_URI']);
        } else {
            $queryString = file_get_contents('php://input');
        }

        try {
            $request = new Net_URL2($returnUrl . '?' . $queryString);
            $message = new OpenID_Message($queryString, OpenID_Message::FORMAT_HTTP);
            $mode    = $message->get('openid.mode');

            if ($mode == 'cancel') {
                throw new SemanticScuttle_Exception_User(
                    'OpenID login cancelled', 10
                );
            } else if ($mode == 'setup_needed') {
                throw new SemanticScuttle_Exception_User(
                    'Immediate OpenID login not possible', 12
                );
            }

            $result = $rp->verify($request, $message);
            if (!$result->success()) {
                throw new SemanticScuttle_Exception_User(
                    'OpenID verification failed', 13
                );
            }

            $identifier = $message->get('openid.claimed_id');
        } catch (OpenID_Exception $e) {
            throw new SemanticScuttle_Exception_User(
                'Unknown OpenID error', 14, $e
            );
        }

        try {
            $identifier = OpenID::normalizeIdentifier($identifier);
        } catch (OpenID_Exception $e) {
            throw new SemanticScuttle_Exception_User(
                'Invalid OpenID identifier', 11, $e
            );
        }

        return array(
            'identifier' => $identifier,
            'userId'     => $this->getUserId($identifier),
            'email'      => $message->get('openid.sreg.email'),
            'nickname'   => $message->get('openid.sreg.nickname'),
            'fullname'   => $message->get('openid.sreg.fullname'),
        );
    }

    /**
     * Returns the user ID for the given OpenID identifier.
     *
     * @param string $identifier OpenID identifier (URL)
     *
     * @return integer User ID or NULL if not found
     *
     * @throws SemanticScuttle_Exception_User When the identifier is invalid
     */
    public function getUserId($identifier)
    {
        $query = 'SELECT sc_users_openids.uId'
            . ' FROM sc_users_openids JOIN sc_users USING (uId)'
            . ' WHERE url = "' . $this->db->sql_escape($identifier) . '"';

        if (!($dbresult = $this->db->sql_query($query))) {
            message_die(
                GENERAL_ERROR,
                'Could not get user',
                '', __LINE__, __FILE__, $query, $this->db
            );
        }
        $row = $this->db->sql_fetchrow($dbresult);
        $this->db->sql_freeresult($dbresult);
        if (!$row) {
            //OpenID not found in database.
            return null;
        }
        return $row['uId'];
    }

    /**
     * Add an OpenID to a given user
     *
     * @param integer $uId        User ID to attach the OpenID to
     * @param string  $identifier OpenID identifier (URL)
     * @param string  $email      OpenID-delivered e-mail address of the user
     *
     * @return boolean True if it worked, false if not
     */
    public function register($uId, $identifier, $email = null)
    {
        //FIXME: use email when google-openid
        $query = 'INSERT INTO ' . $this->getTableName()
            . ' ' . $this->db->sql_build_array(
                'INSERT', array(
                    'uId' => $uId,
                    'url' => $identifier,
                )
            );
        if (!($dbresult = $this->db->sql_query($query))) {
            message_die(
                GENERAL_ERROR, 'Could not register OpenID',
                '', __LINE__, __FILE__, $query, $this->db
            );
            return false;
        }

        return true;
    }

    /**
     * Deletes the OpenID with the given numeric database ID
     *
     * @param integer $id Numeric ID from database
     *
     * @return boolean True if it worked, false if not
     */
    public function delete($id)
    {
        if ($id instanceof SemanticScuttle_Model_OpenId) {
            $id = $id->id;
        }
        $id = (int)$id;

        $query = 'DELETE FROM ' . $this->getTableName()
            .' WHERE id = ' . $id;

        if (!($dbresult = $this->db->sql_query($query))) {
            message_die(
                GENERAL_ERROR, 'Could not delete OpenID',
                '', __LINE__, __FILE__, $query, $this->db
            );
            return false;
        }

        return true;
    }

    /**
     * Loads an OpenID object from the given identifiert
     *
     * @param string $identifier OpenID identifier (URL)
     *
     * @return SemanticScuttle_Model_OpenId OpenID object or NULL if not found
     */
    public function getId($identifier)
    {
        $query = 'SELECT * FROM ' . $this->getTableName()
            . ' WHERE url = "' . $this->db->sql_escape($identifier) . '"';
        if (!($dbresult = $this->db->sql_query($query))) {
            message_die(
                GENERAL_ERROR, 'Could not load OpenID',
                '', __LINE__, __FILE__, $query, $this->db
            );
            return null;
        }

        if ($row = $this->db->sql_fetchrow($dbresult)) {
            $cert = SemanticScuttle_Model_OpenId::fromDb($row);
        } else {
            $cert = null;
        }
        $this->db->sql_freeresult($dbresult);
        return $cert;
    }

    /**
     * Fetch all OpenIDs the given user has attached
     *
     * @param integer $uId  User ID to fetch registered OpenIDs for
     *
     * @return array Array of SemanticScuttle_Model_OpenId objects
     */
    public function getIds($uId)
    {
        $query = 'SELECT * FROM ' . $this->getTableName()
            . ' WHERE uId = ' . (int)$uId
            . ' ORDER BY url ASC';
        if (!($dbresult = $this->db->sql_query($query))) {
            message_die(
                GENERAL_ERROR, 'Could not load OpenIDs',
                '', __LINE__, __FILE__, $query, $this->db
            );
            return array();
        }

        $certs = array();
        while ($row = $this->db->sql_fetchrow($dbresult)) {
            $certs[] = SemanticScuttle_Model_OpenId::fromDb($row);
        }
        $this->db->sql_freeresult($dbresult);
        return $certs;
    }
}
?>