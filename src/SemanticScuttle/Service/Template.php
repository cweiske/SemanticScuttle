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
    protected $basedir;

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

    public function __construct()
    {
        $this->basedir = $GLOBALS['TEMPLATES_DIR'];
    }

    /**
     * Loads and displays a template file.
     *
     * @param string $template Template filename relative
     *                         to template dir
     * @param array  $vars     Array of template variables.
     *
     * @return Template Template object
     */
    function loadTemplate($template, $vars = null)
    {
        if (substr($template, -4) != '.php') {
            $template .= '.php';
        }
        $tpl = new Template($this->basedir .'/'. $template, $vars, $this);
        $tpl->parse();

        return $tpl;
    }
}

class Template
{
    var $vars = array();
    var $file = '';
    var $templateservice;

    function Template($file, $vars = null, &$templateservice)
    {
        $this->vars = $vars;
        $this->file = $file;
        $this->templateservice = $templateservice;
    }

    function parse() {
        if (isset($this->vars))
        extract($this->vars);
        include($this->file);
    }

    function includeTemplate($name) {
        return $this->templateservice->loadTemplate($name, $this->vars);
    }
}
?>