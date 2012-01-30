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
 * OpenID model. Represents one single OpenID association to a user.
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  AGPL http://www.gnu.org/licenses/agpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */
class SemanticScuttle_Model_OpenId
{
    public $id;
    public $uId;
    public $url;



    /**
     * Creates and returns a new object and fills it with
     * the passed values from the database.
     *
     * @param array $arOpenIdRow Database row array
     *
     * @return SemanticScuttle_Model_OpenId
     */
    public static function fromDb($arOpenIdRow)
    {
        $openId = new self();
        foreach (get_object_vars($openId) as $variable => $dummy) {
            if (isset($arOpenIdRow[$variable])) {
                $openId->$variable = $arOpenIdRow[$variable];
            }
        }
        return $openId;
    }

    /**
     * Returns if this OpenID is the one the user is logged in with currently.
     *
     * @return boolean True if the User logged in with this OpenID
     */
    public function isCurrent()
    {
        //FIXME
        return false;
    }
}