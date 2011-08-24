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
        $extract->addArgument(
            'target', array(
                'description' => 'Path to store file contents. Defaults to stdout',
                'optional'    => true,
            )
        );

        $run = $ccl->addCommand('run', array('aliases' => array('r')));
        $run->addArgument(
            'file', array('description' => 'Path of file to extract')
        );

        try {
            $result = $ccl->parse();
            if ($result->command_name == '') {
                $ccl->displayUsage();
            }
            $method = $result->command_name . 'Action';
            $this->$method($result->command->args, $result->command->options);
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

    /**
     * Extract the file given in $args['file'] to stdout
     *
     * @param array $args    Array of commandline arguments
     * @param array $options Array of commandline options
     *
     * @return void
     * @throws Exception When the file does not exist in the .phar
     */
    public function extractAction($args, $options)
    {
        $file = 'phar://SemanticScuttle.phar/' . $args['file'];
        if (!file_exists($file)) {
            echo 'File "' . $args['file'] . '" does not exist in phar.' . "\n";
            exit(1);
        }

        if (isset($args['target'])) {
            copy($file, $args['target']);
        } else {
            readfile($file);
        }
    }

    /**
     * Runs a script inside the .phar
     *
     * @param array $args    Array of commandline arguments
     * @param array $options Array of commandline options
     *
     * @return void
     * @throws Exception When the file does not exist in the .phar
     */
    public function runAction($args, $options)
    {
        $file = 'phar://SemanticScuttle.phar/' . $args['file'];
        if (!file_exists($file)) {
            echo 'File "' . $args['file'] . '" does not exist in phar.' . "\n";
            exit(1);
        }

        //FIXME: shift off options
        include $file;
    }
}
?>