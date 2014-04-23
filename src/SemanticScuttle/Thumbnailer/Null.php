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
 * Dummy thumbnailer that never returns a thumbnail URL
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */
class SemanticScuttle_Thumbnailer_Null
{
    /**
     * Set dummy configuration
     *
     * @param array $config Dummy configuration
     *
     * @return void
     */
    public function setConfig($config)
    {
    }

    /**
     * Get the URL for a website thumbnail.
     * Always returns false.
     *
     * @param string  $bookmarkUrl URL of website to create thumbnail for
     * @param integer $width       Screenshot width
     * @param integer $height      Screenshot height
     *
     * @return mixed FALSE when no screenshot could be obtained,
     *               string with the URL otherwise
     */
    public function getThumbnailUrl($bookmarkUrl, $width, $height)
    {
        return false;
    }
}
?>
