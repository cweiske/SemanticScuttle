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
     * tha passed values from the database.
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

}
?>