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
class SemanticScuttle_Config implements ArrayAccess
{
    /**
     * Prefix for configuration files.
     * Used to inject stream wrapper protocol for unit testing
     *
     * @var string
     */
    public $filePrefix = '';

    /**
     * Array with all configuration data
     */
    protected $configData = array();


    /**
     * Loads the variables from the given configuration file
     * into $GLOBALS
     *
     * @param string $file Configuration file path to load
     *
     * @return void
     */
    public function load($file)
    {
        // ack \\$ data/config.default.php |sed 's/ .*$//'|grep -v ^$|sort
        //this here is required because setting $GLOBALS doesnt'
        // automatically set "global" :/
        global
            $adminemail,
            $adminsAreAdvisedTagsFromOtherAdmins,
            $adminsCanModifyBookmarksFromOtherUsers,
            $admin_users,
            $allowedProtocols,
            $allowUnittestMode,
            $antispamAnswer,
            $antispamQuestion,
            $authDebug,
            $authEmailSuffix,
            $authOptions,
            $authType,
            $avahiServiceFilePath,
            $avahiServiceFilePrefix,
            $avahiTagName,
            $blankDescription,
            $bottom_include,
            $cleanurls,
            $dateOrderField,
            $dbhost,
            $dbname,
            $dbneedssetnames,
            $dbpass,
            $dbpersist,
            $dbport,
            $dbtype,
            $dbuser,
            $debugMode,
            $defaultOrderBy,
            $defaultPerPage,
            $defaultPerPageForAdmins,
            $defaultRecentDays,
            $defaultRssEntries,
            $defaults,
            $descriptionAnchors,
            $dir_cache,
            $enableAdminColors,
            $enableCommonBookmarkDescription,
            $enableCommonTagDescription,
            $enableCommonTagDescriptionEditedByAll,
            $enableGoogleCustomSearch,
            $enableRegistration,
            $enableVoting,
            $enableWebsiteThumbnails,
            $filetypes,
            $footerMessage,
            $googleAnalyticsCode,
            $hideBelowVoting,
            $index_sidebar_blocks,
            $locale,
            $longdate,
            $maxRssEntries,
            $maxSizeMenuBlock,
            $menu2Tags,
            $menuTag,
            $nofollow,
            $reservedusers,
            $root,
            $serviceoverrides,
            $shortdate,
            $shorturl,
            $sidebarBottomMessage,
            $sidebarTopMessage,
            $sitename,
            $sizeSearchHistory,
            $tableprefix,
            $TEMPLATES_DIR,
            $theme,
            $thumbnailsKey,
            $thumbnailsUserId,
            $top_include,
            $unittestUrl,
            $url_redir,
            $usecache,
            $useredir,
            $votingMode,
            $welcomeMessage;

        require_once $file;

        //make them global
        //does not really work because many parts still access the variables
        // without $cfg/$GLOBALS
        //unset($file);
        //$GLOBALS = get_defined_vars() + $GLOBALS;
    }

    /**
     * Assigns GLOBALS to configData
     */
    public function __construct()
    {
        $this->configData =& $GLOBALS;
    }



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
     * Creates an array with file paths where the configuration
     * file may be located.
     *
     * When open_basedir restrictions are in effect and /etc is not part of
     * the setting, /etc/semanticscuttle/ is not checked for config files.
     *
     * @return array Array of possible configuration file paths.
     */
    public function getPossibleConfigFiles()
    {
        if (isset($_SERVER['HTTP_HOST'])) {
            //use basename to prevent path injection
            $host = basename($_SERVER['HTTP_HOST']);
        } else {
            $host = 'cli';
        }
        $datadir = $this->getDataDir();
        $arFiles = array();

        if (class_exists('Phar') && Phar::running(false) != '') {
            $arFiles[] = Phar::running(false) . '.config.php';
        }

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

        return $arFiles;
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
     * @return array Array with config file path as first value
     *               and default config file path as second value.
     *               Any may be NULL if not found
     */
    public function findFiles()
    {
        $datadir = $this->getDataDir();
        $arFiles = $this->getPossibleConfigFiles();

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



    public function offsetExists($offset)
    {
        return isset($this->configData[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->configData[$offset];
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->configData[] = $value;
        } else {
            $this->configData[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->configData[$offset]);
    }
}

?>