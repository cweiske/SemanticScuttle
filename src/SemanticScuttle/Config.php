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
 * Configuration handling
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  AGPL http://www.gnu.org/licenses/agpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */
class SemanticScuttle_Config
{
    /**
     * Prefix for configuration files.
     * Used to inject stream wrapper protocol for unit testing
     *
     * @var string
     */
    public $filePrefix = '';



    /**
     * Finds the correct data directory
     *
     * @return string Full path to the data directory with a trailing slash
     */
    protected function getDataDir()
    {
        if ('@data_dir@' == '@' . 'data_dir@') {
            //non pear-install
            $datadir = dirname(__FILE__) . '/../../data/';
        } else {
            //pear installation; files are in include path
            $datadir = '@data_dir@/SemanticScuttle/';
        }

        return $datadir;
    }



    /**
     * Tries to find a configuration file by looking in different
     * places:
     * - pear data_dir/SemanticScuttle/config-$hostname.php
     * - pear data_dir/SemanticScuttle/config.php
     * - /etc/semanticscuttle/config-$hostname.php
     * - /etc/semanticscuttle/config.php
     *
     * Paths with host name have priority.
     *
     * When open_basedir restrictions are in effect and /etc is not part of
     * the setting, /etc/semanticscuttle/ is not checked for config files.
     *
     * @return array Array with config file path as first value
     *               and default config file path as second value.
     *               Any may be NULL if not found
     */
    public function findFiles()
    {
        //use basename to prevent path injection
        $host = basename($_SERVER['HTTP_HOST']);
        $datadir = $this->getDataDir();

        $openbase = ini_get('open_basedir');
        if ($openbase && strpos($openbase, '/etc') === false) {
            //open_basedir restrictions enabled and /etc not allowed?
            // then don't look in /etc for config files.
            // the check is not perfect, but it covers most cases
            $arFiles = array(
                $datadir . 'config.' . $host . '.php',
                $datadir . 'config.php',
            );
        } else {
            $arFiles = array(
                $datadir . 'config.' . $host . '.php',
                '/etc/semanticscuttle/config.' . $host . '.php',
                $datadir . 'config.php',
                '/etc/semanticscuttle/config.php',
            );
        }

        $configfile = null;
        foreach ($arFiles as $file) {
            if (file_exists($this->filePrefix . $file)) {
                $configfile = $file;
                break;
            }
        }

        //find default file
        $arDefaultFiles = array_unique(
            array(
                substr($configfile, 0, -3) . 'default.php',
                $datadir . 'config.default.php',
                '/etc/semanticscuttle/config.default.php',
            )
        );
        $defaultfile = null;
        foreach ($arDefaultFiles as $file) {
            if (file_exists($this->filePrefix . $file)) {
                $defaultfile = $file;
                break;
            }
        }
        return array($configfile, $defaultfile);
    }
}

?>