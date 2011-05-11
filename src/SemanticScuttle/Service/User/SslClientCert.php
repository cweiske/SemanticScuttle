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

/**
 * SemanticScuttle SSL client certificate management service
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  AGPL http://www.gnu.org/licenses/agpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */
class SemanticScuttle_Service_User_SslClientCert extends SemanticScuttle_DbService
{
    /**
     * Creates a new instance, sets database variable and table name.
     *
     * @param sql_db $db Database object
     */
    protected function __construct($db)
    {
        $this->db = $db;
        $this->tablename  = $GLOBALS['tableprefix'] .'users_sslclientcerts';
    }

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

    /**
     * Determines if the browser provided a valid SSL client certificate
     *
     * @return boolean True if the client cert is there and is valid
     */
    public function hasValidCert()
    {
        if (!isset($_SERVER['SSL_CLIENT_M_SERIAL'])
            || !isset($_SERVER['SSL_CLIENT_V_END'])
            || !isset($_SERVER['SSL_CLIENT_VERIFY'])
            || $_SERVER['SSL_CLIENT_VERIFY'] !== 'SUCCESS'
            || !isset($_SERVER['SSL_CLIENT_I_DN'])
        ) {
            return false;
        }

        if ($_SERVER['SSL_CLIENT_V_REMAIN'] <= 0) {
            return false;
        }

        return true;
    }



    /**
     * Registers the currently available SSL client certificate
     * with the given user. As a result, the user will be able to login
     * using the certifiate
     *
     * @param integer $uId User ID to attach the client cert to.
     *
     * @return boolean True if registration was well, false if not.
     */
    public function registerCurrentCertificate($uId)
    {
        $serial         = $_SERVER['SSL_CLIENT_M_SERIAL'];
        $clientIssuerDn = $_SERVER['SSL_CLIENT_I_DN'];

        $query = 'INSERT INTO ' . $this->getTableName()
            . ' '. $this->db->sql_build_array(
                'INSERT', array(
                    'uId'               => $uId,
                    'sslSerial'         => $serial,
                    'sslClientIssuerDn' => $clientIssuerDn,
                    'sslName'           => $_SERVER['SSL_CLIENT_S_DN_CN'],
                    'sslEmail'          => $_SERVER['SSL_CLIENT_S_DN_Email']
                )
            );
        if (!($dbresult = $this->db->sql_query($query))) {
            message_die(
                GENERAL_ERROR, 'Could not load user for client certificate',
                '', __LINE__, __FILE__, $query, $this->db
            );
            return false;
        }

        return true;
    }



    /**
     * Takes values from the currently available SSL client certificate
     * and adds the available profile data to the user.
     *
     * @param integer $uId User ID to attach the client cert to.
     *
     * @return array Array of profile data that were registered.
     *               Database column name as key, new value as value
     */
    public function updateProfileFromCurentCert($uId)
    {
        $arData = array();

        if (isset($_SERVER['SSL_CLIENT_S_DN_CN'])
            && trim($_SERVER['SSL_CLIENT_S_DN_CN']) != ''
        ) {
            $arData['name'] = trim($_SERVER['SSL_CLIENT_S_DN_CN']);
        }

        if (count($arData)) {
            $us = SemanticScuttle_Service_Factory::get('User');
            foreach ($arData as $column => $value) {
                $us->_updateuser($uId, $column, $value);
            }
        }
        return $arData;
    }



    /**
     * Tries to detect the user ID from the SSL client certificate passed
     * to the web server.
     *
     * @return mixed Integer user ID if the certificate is valid and
     *               assigned to a user, boolean false otherwise
     */
    public function getUserIdFromCert()
    {
        if (!$this->hasValidCert()) {
            return false;
        }

        $serial         = $_SERVER['SSL_CLIENT_M_SERIAL'];
        $clientIssuerDn = $_SERVER['SSL_CLIENT_I_DN'];

        $query = 'SELECT uId'
            . ' FROM ' . $this->getTableName()
            . ' WHERE sslSerial = \'' . $this->db->sql_escape($serial) . '\''
            . ' AND sslClientIssuerDn = \''
            . $this->db->sql_escape($clientIssuerDn)
            . '\'';
        if (!($dbresult = $this->db->sql_query($query))) {
            message_die(
                GENERAL_ERROR, 'Could not load user for client certificate',
                '', __LINE__, __FILE__, $query, $this->db
            );
            return false;
        }

        $row = $this->db->sql_fetchrow($dbresult);
        $this->db->sql_freeresult($dbresult);

        if (!$row) {
            return false;
        }
        return (int)$row['uId'];
    }



    /**
     * Fetches the certificate with the given ID from database.
     *
     * @param integer $id Certificate ID in database
     *
     * @return SemanticScuttle_Model_User_SslClientCert Certificate object
     *                                                  or null if not found
     */
    public function getCert($id)
    {
        $query = 'SELECT * FROM ' . $this->getTableName()
            . ' WHERE id = ' . (int)$id;
        if (!($dbresult = $this->db->sql_query($query))) {
            message_die(
                GENERAL_ERROR, 'Could not load SSL client certificate',
                '', __LINE__, __FILE__, $query, $this->db
            );
            return null;
        }

        if ($row = $this->db->sql_fetchrow($dbresult)) {
            $cert = SemanticScuttle_Model_User_SslClientCert::fromDb($row);
        } else {
            $cert = null;
        }
        $this->db->sql_freeresult($dbresult);
        return $cert;
    }



    /**
     * Fetches all registered certificates for the user from the database
     * and returns it.
     *
     * @return array Array with all certificates for the user. Empty if
     *               there are none, SemanticScuttle_Model_User_SslClientCert
     *               objects otherwise.
     */
    public function getUserCerts($uId)
    {
        $query = 'SELECT * FROM ' . $this->getTableName()
            . ' WHERE uId = ' . (int)$uId
            . ' ORDER BY sslSerial DESC';
        if (!($dbresult = $this->db->sql_query($query))) {
            message_die(
                GENERAL_ERROR, 'Could not load SSL client certificates',
                '', __LINE__, __FILE__, $query, $this->db
            );
            return array();
        }

        $certs = array();
        while ($row = $this->db->sql_fetchrow($dbresult)) {
            $certs[] = SemanticScuttle_Model_User_SslClientCert::fromDb($row);
        }
        $this->db->sql_freeresult($dbresult);
        return $certs;
    }



    /**
     * Deletes a SSL client certificate.
     * No security checks are made here.
     *
     * @param mixed $cert Certificate object or certificate database id.
     *                    Objects are of type
     *                    SemanticScuttle_Model_User_SslClientCert
     *
     * @return boolean True if all went well, false if it could not be deleted
     */
    public function delete($cert)
    {
        if ($cert instanceof SemanticScuttle_Model_User_SslClientCert) {
            $id = (int)$cert->id;
        } else {
            $id = (int)$cert;
        }

        if ($id === 0) {
            return false;
        }

        $query = 'DELETE FROM ' . $this->getTableName()
            .' WHERE id = ' . $id;

        if (!($dbresult = $this->db->sql_query($query))) {
            message_die(
                GENERAL_ERROR, 'Could not delete user certificate',
                '', __LINE__, __FILE__, $query, $this->db
            );
            return false;
        }

        return true;
    }
}
?>