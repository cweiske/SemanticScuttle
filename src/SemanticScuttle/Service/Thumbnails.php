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
 * Instantiates the configured website thumbnailer object.
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */
class SemanticScuttle_Service_Thumbnails extends SemanticScuttle_Service
{
    /**
     * Instantiates the configured website thumbnailer object.
     *
     * @return object Website thumbnailer
     */
    public function getThumbnailer()
    {
        if (!isset($GLOBALS['thumbnailsType'])
            || $GLOBALS['thumbnailsType'] == ''
        ) {
            $class = 'SemanticScuttle_Thumbnailer_Null';
        } else {
            $class = 'SemanticScuttle_Thumbnailer_'
                . ucfirst($GLOBALS['thumbnailsType']);
        }
        if (!class_exists($class)) {
            //PEAR classname to filename rule
            $file = str_replace('_', '/', $class) . '.php';
            include_once $file;
        }

        $thumbnailer = new $class();

        if (!isset($GLOBALS['thumbnailsConfig'])
            || $GLOBALS['thumbnailsConfig'] == ''
        ) {
            $thumbnailer->setConfig(null);
        } else {
            $thumbnailer->setConfig($GLOBALS['thumbnailsConfig']);
        }

        return $thumbnailer;
    }
}
?>
