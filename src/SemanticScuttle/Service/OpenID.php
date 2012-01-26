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
class SemanticScuttle_Service_OpenID extends SemanticScuttle_DbService
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
     * @return SemanticScuttle_Service_OpenID
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
     * Part 1 of the OpenID login process: Send user to his identity provider.
     *
     * This method exits the PHP process.
     *
     * @param string $identifier OpenID URL
     * @param string $returnUrl  URL the identity provider shall send the user
     *                           back to
     *
     * @return void No return value needed since it exits.
     *
     * @throws SemanticScuttle_Exception_User When something goes wrong
     */
    public function sendIdRequest($identifier, $returnUrl)
    {
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
            //FIXME: report error
            var_dump($e);die();
            throw $e;
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
}
?>