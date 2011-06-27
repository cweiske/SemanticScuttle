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
    /**
     * Status "public" / visible for all
     */
    const SPUBLIC = 0;

    /**
     * Status "shared" / visible for people on your watchlist
     */
    const SWATCHLIST = 1;

    /**
     * Status "private" / visible for yourself only
     */
    const SPRIVATE = 2;



    /**
     * Checks if the given URL is valid and may be used with this
     * SemanticScuttle installation.
     *
     * @param string $url URL to verify.
     *
     * @return boolean True if the URL is allowed, false if not
     */
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