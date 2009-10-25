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

require_once 'PHPUnit/Framework.php';

/**
 * Base unittest class that provides several helper methods.
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author Christian Weiske <cweiske@cweiske.de>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */
class TestBase extends PHPUnit_Framework_TestCase
{
    /**
     * Create a new bookmark.
     *
     * @return integer ID of bookmark
     */
    protected function addBookmark()
    {
        $bs = SemanticScuttle_Service_Factory::get('Bookmark');
        $rand = rand();
        $bid = $bs->addBookmark(
            'http://example.org/' . $rand,
            'unittest bookmark #' . $rand,
            'description',
            null,
            0,
            array('unittest')
        );
        return $bid;
    }

}

?>