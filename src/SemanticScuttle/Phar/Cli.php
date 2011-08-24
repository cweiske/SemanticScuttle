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
require_once 'Console/CommandLine.php';

/**
 * Command line interface for the SemanticScuttle.phar file.
 * Can be used to extract parts of the phar file and to run
 * scripts in it.
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  AGPL http://www.gnu.org/licenses/agpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */
class SemanticScuttle_Phar_Cli
{
    public function run()
    {
        $ccl = new Console_CommandLine();
        $ccl->name        = 'SemanticScuttle';
        $ccl->description = 'Command line interface to SemanticScuttle .phar files';
        $ccl->version            = '0.98.3';
        $ccl->add_help_option    = true;
        $ccl->add_version_option = true;
        $ccl->force_posix        = true;

        $ccl->addCommand('list', array('aliases' => array('l')));

        $extract = $ccl->addCommand('extract', array('aliases' => array('x')));
        $extract->addArgument(
            'file', array('description' => 'Path of file to extract')
        );

        $run = $ccl->addCommand('run', array('aliases' => array('r')));
        $run->addArgument(
            'file', array('description' => 'Path of file to extract')
        );

        try {
            $result = $ccl->parse();
            $method = $result->command_name . 'Action';
            $this->$method($result->args, $result->options);
        } catch (Exception $ex) {
            $ccl->displayError($ex->getMessage());
        }
    }


    /**
     * Lists the contents of this phar archive and echos the output
     *
     * @return void
     */
    public function listAction()
    {
        $excludes = array(
            'data/locales',
            'data/templates',
            'src',
            'www',
        );
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                'phar://SemanticScuttle.phar/'
            )
        );
        while($it->valid()) {
            if (!$it->isDot()) {
                $name = $it->getSubPathName();
                $excluded = false;
                foreach ($excludes as $exclude) {
                    if (substr($name, 0, strlen($exclude)) == $exclude) {
                        $excluded = true;
                        break;
                    }
                }
                if (!$excluded) {
                    echo $name . "\n";
                }
            }
            $it->next();
        }
    }


    public function runAction($args, $options)
    {
        //FIXME
    }


    public function extractAction($args, $options)
    {
        //FIXME
    }
}
?>