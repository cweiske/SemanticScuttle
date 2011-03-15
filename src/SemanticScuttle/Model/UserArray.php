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
 * Mostly static methods that help working with a user row array from database.
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */
class SemanticScuttle_Model_UserArray
{
    /**
     * Returns full user name as specified in the profile if it is set,
     * otherwise the nickname/loginname is returned.
     *
     * @param array $row User row array from database
     *
     * @return string Full name or username
     */
    public static function getName($row)
    {
        if (isset($row['name']) && $row['name']) {
            return $row['name'];
        }
        return $row['username'];
    }
}
?>