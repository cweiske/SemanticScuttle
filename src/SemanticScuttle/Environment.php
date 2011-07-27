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
        /* old code that does not work today.
           if you find that this code helps you, tell us
           and send us the output of var_export($_SERVER);
        // Correct bugs with PATH_INFO (maybe for Apache 1 or CGI) -- for 1&1 host...
        if (isset($_SERVER['PATH_INFO']) && isset($_SERVER['ORIG_PATH_INFO'])) {
            if (strlen($_SERVER["PATH_INFO"])<strlen($_SERVER["ORIG_PATH_INFO"])) {
                $_SERVER["PATH_INFO"] = $_SERVER["ORIG_PATH_INFO"];
            }
            if (strcasecmp($_SERVER["PATH_INFO"], $_SERVER["SCRIPT_NAME"]) == 0) {
                unset($_SERVER["PATH_INFO"]);
            }
            if (strpos($_SERVER["PATH_INFO"], '.php') !== false) {
                unset($_SERVER["PATH_INFO"]);
            }
        }
        */

        return $_SERVER['PATH_INFO'];
    }
}
?>