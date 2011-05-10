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
 * SSL client certificate model. Represents one single client certificate
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  AGPL http://www.gnu.org/licenses/agpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */
class SemanticScuttle_Model_User_SslClientCert
{
    public $id;
    public $uId;
    public $sslSerial;
    public $sslClientIssuerDn;
    public $sslName;
    public $sslEmail;



    /**
     * Creates and returns a new object and fills it with
     * the passed values from the database.
     *
     * @param array $arCertRow Database row array
     *
     * @return SemanticScuttle_Model_User_SslClientCert
     */
    public static function fromDb($arCertRow)
    {
        $cert = new self();
        foreach (get_object_vars($cert) as $variable => $dummy) {
            if (isset($arCertRow[$variable])) {
                $cert->$variable = $arCertRow[$variable];
            }
        }
        return $cert;
    }



    /**
     * Loads the user's/browser's client certificate information into
     * an object and returns it.
     * Expects that all information is available.
     * Better check with
     * SemanticScuttle_Service_User_SslClientCert::hasValidCert() before.
     *
     * @return SemanticScuttle_Model_User_SslClientCert
     *
     * @see SemanticScuttle_Service_User_SslClientCert::hasValidCert()
     */
    public static function fromCurrentCert()
    {
        $cert = new self();
        $cert->sslSerial         = $_SERVER['SSL_CLIENT_M_SERIAL'];
        $cert->sslClientIssuerDn = $_SERVER['SSL_CLIENT_I_DN'];
        $cert->sslName           = $_SERVER['SSL_CLIENT_S_DN_CN'];
        $cert->sslEmail          = $_SERVER['SSL_CLIENT_S_DN_Email'];
        return $cert;
    }



    /**
     * Tells you if this certificate is the one the user is currently browsing
     * with.
     *
     * @return boolean True if this certificate is the current browser's
     */
    public function isCurrent()
    {
        if (!isset($_SERVER['SSL_CLIENT_M_SERIAL'])
            || !isset($_SERVER['SSL_CLIENT_I_DN'])
        ) {
            return false;
        }

        return $this->sslSerial == $_SERVER['SSL_CLIENT_M_SERIAL']
            && $this->sslClientIssuerDn == $_SERVER['SSL_CLIENT_I_DN'];
    }



    /**
     * Checks if this certificate is registered (exists) in the certificate
     * array
     *
     * @param array $arCertificates Array of certificate objects
     *
     * @return boolean True or false
     */
    public function isRegistered($arCertificates)
    {
        foreach ($arCertificates as $cert) {
            if ($cert->equals($this)) {
                return true;
            }
        }
        return false;
    }



    /**
     * Deletes this certificate from database
     *
     * @return boolean True if all went well, false if not
     */
    public function delete()
    {
        $ok = SemanticScuttle_Service_Factory::get('User_SslClientCert')
            ->delete($this);
        if ($ok) {
            $this->id = null;
        }
        return $ok;
    }



    /**
     * Compares this certificate with the given one.
     *
     * @param SemanticScuttle_Service_Factory $cert Another user certificate
     *
     * @return boolean True if both match.
     */
    public function equals(SemanticScuttle_Model_User_SslClientCert $cert)
    {
        return $this->sslSerial == $cert->sslSerial
            && $this->sslClientIssuerDn == $cert->sslClientIssuerDn;
    }
}
?>