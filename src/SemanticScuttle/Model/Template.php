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
 * SemanticScuttle HTML templating system.
 * This templating system is really, really simple and based
 * on including php files while proving a set of
 * variables in the template scope.
 * When rendering templates, they are directly echoed to the
 * browser. There is no in-built way to capture their output.
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Benjamin Huynh-Kim-Bang <mensonge@users.sourceforge.net>
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @author   Eric Dane <ericdane@users.sourceforge.net>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */
class SemanticScuttle_Model_Template
{
    /**
     * Array of variables to be available in template
     * scope.
     *
     * @var array
     */
    protected $vars = array();

    /**
     * File name of template
     */
    protected $file = '';

    /**
     * Template service instance
     *
     * @var SemanticScuttle_Service_Template
     */
    protected $ts;



    /**
     * Create a new template instance
     *
     * @param string                           $file Template filename,
     *                                               full path
     * @param array                            $vars Template variables
     * @param SemanticScuttle_Service_Template $ts   Template service
     */
    public function __construct(
        $file, $vars = null,
        SemanticScuttle_Service_Template $ts = null
    ) {
        $this->vars = $vars;
        $this->file = $file;
        $this->ts   = $ts;
    }



    /**
     * Sets variables and includes the template file,
     * causing it to be rendered.
     *
     * Does not take care of themes and so.
     * The include path must be set so the correct theme is used.
     *
     * @return void
     */
    public function parse()
    {
        if (isset($this->vars)) {
            extract($this->vars);
        }
        include $this->file;
    }



    /**
     * Loads another template
     *
     * @param string $file Filename of template, relative
     *                     to template directory
     *
     * @return SemanticScuttle_Service_Template Template object
     */
    public function includeTemplate($file)
    {
        return $this->ts->loadTemplate($file, $this->vars);
    }
}
?>