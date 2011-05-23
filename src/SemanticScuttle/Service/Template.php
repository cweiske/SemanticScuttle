<?php
/**
 * SemanticScuttle - your social bookmark manager.
 *
 * PHP version 5.
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Benjamin Huynh-Kim-Bang <mensonge@users.sourceforge.net>
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @author   Eric Dane <ericdane@users.sourceforge.net>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */

require_once 'SemanticScuttle/Model/Template.php';

/**
 * SemanticScuttle template service.
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Benjamin Huynh-Kim-Bang <mensonge@users.sourceforge.net>
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @author   Eric Dane <ericdane@users.sourceforge.net>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */
class SemanticScuttle_Service_Template extends SemanticScuttle_Service
{
    /**
     * Full path to template directory.
     *
     * Set in constructor to
     * $GLOBALS['TEMPLATES_DIR']
     *
     * @var string
     */
    protected $basedir;

    /**
     * The template theme to use.
     * Set in constructor from $GLOBALS['theme']
     *
     * @var string
     */
    protected $theme;



    /**
     * Returns the single service instance
     *
     * @param DB $db Database object
     *
     * @return SemanticScuttle_Service
     */
    public static function getInstance($db)
    {
        static $instance;
        if (!isset($instance)) {
            $instance = new self($db);
        }
        return $instance;
    }



    /**
     * Create a new instance
     */
    protected function __construct()
    {
        $this->basedir = $GLOBALS['TEMPLATES_DIR'];
        $this->theme   = $GLOBALS['theme'];
        //FIXME: verify the theme exists
    }



    /**
     * Loads and displays a template file.
     *
     * @param string $template Template filename relative
     *                         to template dir
     * @param array  $vars     Array of template variables.
     *
     * @return SemanticScuttle_Model_Template Template object
     */
    public function loadTemplate($template, $vars = null)
    {
        if (substr($template, -4) != '.php') {
            $template .= '.php';
        }

        $oldIncPath = get_include_path();
        set_include_path(
            $this->basedir . $this->theme
            . PATH_SEPARATOR . $this->basedir . 'default'
            //needed since services are instantiated in templates
            . PATH_SEPARATOR . $oldIncPath
        );

        $tpl = new SemanticScuttle_Model_Template(
            $template, $vars, $this
        );
        $tpl->parse();

        set_include_path($oldIncPath);

        return $tpl;
    }
}

?>