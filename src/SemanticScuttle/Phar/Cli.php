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
        $this->listAction();
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


    public function runAction()
    {
        //FIXME
    }


    public function extractAction()
    {
        //FIXME
    }
}
?>