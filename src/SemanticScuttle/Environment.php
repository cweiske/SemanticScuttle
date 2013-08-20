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
 * Server environment handling methods
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  AGPL http://www.gnu.org/licenses/agpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */
class SemanticScuttle_Environment
{
    /**
     * Determines the correct $_SERVER['PATH_INFO'] value
     *
     * @return string New value
     */
    public static function getServerPathInfo()
    {
        if (isset($_SERVER['PATH_INFO'])) {
            return $_SERVER['PATH_INFO'];
        }

        if (isset($_SERVER['ORIG_PATH_INFO'])) {
            //1&1 servers
            if ($_SERVER['ORIG_PATH_INFO'] == $_SERVER['SCRIPT_NAME']) {
                return '';
            }
            return $_SERVER['ORIG_PATH_INFO'];
        }

        //fallback when no special path after the php file is given
        return '';
    }
}
?>