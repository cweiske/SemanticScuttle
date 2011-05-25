<?php
/**
 * SemanticScuttle - your social bookmark manager.
 *
 * PHP version 5.
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  AGPL v3 or later http://www.gnu.org/licenses/agpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */

/**
 * A theme, the visual representation of SemanticScuttle.
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  AGPL v3 or later http://www.gnu.org/licenses/agpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */
class SemanticScuttle_Model_Theme
{
    /**
     * Theme name. Also the path part of template and resource files
     *
     * @var string
     */
    protected $name = null;

    /**
     * Local path to the www themes directory.
     * Needs to have a trailing slash.
     *
     * @var string
     */
    protected $wwwThemeDir = null;



    /**
     * Create a new theme instance.
     *
     * @param string $name Theme name "data/templates/(*)/"
     */
    public function __construct($name = 'default')
    {
        $this->name = $name;
        $this->wwwThemeDir = $GLOBALS['wwwdir'] . '/themes/';
        //TODO: implement theme hierarchies with parent fallback
    }



    /**
     * Returns the URL path to a resource file (www/themes/$name/$file).
     * Automatically falls back to the parent theme if the file does not exist
     * in the theme.
     *
     * Must always be used when adding i.e. images to the output.
     *
     * @param string $file File name to find the path for, i.e. "scuttle.css".
     *
     * @return string Full path
     */
    public function resource($file)
    {
        $themeFile = $this->wwwThemeDir . $this->name . '/' . $file;
        if (file_exists($themeFile)) {
            return ROOT . 'themes/' . $this->name . '/' . $file;
        }

        $defaultFile = $this->wwwThemeDir . 'default/' . $file;
        if (file_exists($defaultFile)) {
            return ROOT . 'themes/default/' . $file;
        }

        //file does not exist. fall back to the theme file
        // to guide the theme author a bit.
        // TODO: logging. in admin mode, there should be a message
        return ROOT . 'themes/' . $this->name . '/' . $file;
    }



    /**
     * Returns the theme name.
     *
     * @return string Theme name
     */
    public function getName()
    {
        return $this->name;
    }
}
?>