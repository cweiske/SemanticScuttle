<?php
/**
 * SemanticScuttle - your social bookmark manager.
 *
 * PHP version 5.
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */

/**
 * Bookmark model class, keeping the data of a single bookmark.
 * It will slowly replace the old array style format.
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */
class SemanticScuttle_Model_Bookmark
{
    public static function isValidUrl($url)
    {
        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (array_search($scheme, $GLOBALS['allowedProtocols']) === false) {
            return false;
        }
        return true;
    }

}


?>