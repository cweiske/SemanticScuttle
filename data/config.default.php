<?php
/**
 * Default configuration file for SemanticScuttle
 *
 * This file is included just before config.php.
 * If there is something you want to change, copy the lines
 * in your personal config.php file.
 * Do not modify _this_ file!
 *
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 *
 * @link http://sourceforge.net/projects/semanticscuttle/
 */

/**
 * Array for defaults.
 *
 * @var array
 */
$defaults = array();


/***************************************************
 * HTML output configuration
 */

/**
 * The name of this site.
 *
 * @var string
 */
$sitename = 'SemanticScuttle';

/**
 * The welcome message on the homepage.
 *
 * @var string
 */
$welcomeMessage = 'Welcome to SemanticScuttle!'
    . ' Social bookmarking for small communities.';

/**
 * HTML message appearing at the bottom of the page.
 * (just above SemanticScuttle credits)
 *
 * @var string
 */
$footerMessage = '';

/**
 * HTML message appearing at the top of the sidebar
 *
 * @var string
 */
$sidebarTopMessage  = '';

/**
 * HTML message appearing at the bottom of the sidebar
 *
 * @var string
 */
$sidebarBottomMessage = '';

/**
 * The HTML theme to use. With themes, you can give your semanticscuttle
 * installation a new look.
 *
 * Themes are the folders in data/templates/
 *
 * @var string
 */
$theme = 'default';


/***************************************************
 * System configuration
 */


/**
 * SemanticScuttle root directory.
 * Set to NULL to autodetect the root url of the website.
 *
 * If your installation is in a subdirectory like
 * "http://www.example.com/semanticscuttle/" then
 * replace NULL by your address (between "" and with trailing '/')
 *
 * The autodetection works for both HTTP and HTTPS urls.
 * If you offer HTTP *only*, then set your root url here.
 *
 * @var string
 */
$root = null;

/**
 * Translation from locales/ folder.
 *
 * Examples: de_DE, en_GB, fr_FR
 *
 * @var string
 */
$locale = 'en_GB';

/**
 * If the cache shall be used (true/false)
 *
 * @var boolean
 */
$usecache = false;

/**
 * Cache directory.
 *
 * @var string
 */
$dir_cache = dirname(__FILE__) . '/cache/';

/**
 * Use clean urls without .php filenames.
 * Requires mod_rewrite (for Apache) to be active.
 *
 * @var boolean
 */
$cleanurls = false;

/**
 * Show debug messages.
 * This setting is recommended when setting up SemanticScuttle,
 * and when hacking on it.
 *
 * @var boolean
 */
$debugMode = false;



/***************************************************
 * Database configuration
 */

/**
 * Database driver
 *
 * available:
 * mysql4, mysqli, mysql, oracle, postgres, sqlite, db2, firebird,
 * mssql, mssq-odbc
 *
 * @var string
 */
$dbtype = 'mysql4';

/**
 * Database hostname/IP
 *
 * @var string
 */
$dbhost = '127.0.0.1';

/**
 * Database port.
 *
 * When using mysqli, leave this to null
 * - connecting will fail otherwise.
 *
 * @var string|integer
 */
$dbport = null;

/**
 * Database username
 *
 * @var string
 */
$dbuser = 'username';

/**
 * Database password
 *
 * @var string
 */
$dbpass = 'password';


/**
 * Name of database
 *
 * @var string
 */
$dbname = 'scuttle';

/**
 * Database table name prefix.
 * Do not use "-" since this is badly handled by MySQL.
 *
 * @var string
 */
$tableprefix = 'sc_';

/*
 * If the database needs to be switched to UTF8
 * manually or not. If true, a "SET NAMES UTF8" query
 * will be sent at the beginning. If you need performance,
 * save this query and set it in your mysql server options.
 *
 * @var boolean
 */
$dbneedssetnames = true;


/***************************************************
 * Users
 */

/**
 * Contact address for the site administrator.
 * Used as the FROM address in password retrieval e-mails.
 *
 * @var string
 */
$adminemail = 'admin@example.org';

/**
 * Array of user names who have admin rights
 *
 * Example:
 * <code>
 * $admin_users = array('adminnickname', 'user1nick', 'user2nick');
 * </code>
 *
 * @var array
 */
$admin_users = array();

/**
 * If admin users can edit or delete bookmarks belonging to other users.
 *
 * @var boolean
 */
$adminsCanModifyBookmarksFromOtherUsers = true;

/**
 * If tags from other admins are proposed to each admin
 * (in add/edit a bookmark page).
 *
 * @var boolean
 */
$adminsAreAdvisedTagsFromOtherAdmins = false;

/**
 * Array of usernames that cannot be registered
 *
 * @var array
 */
$reservedusers  = array('all', 'watchlist');




/***************************************************
 * Anti SPAM measures
 */

/**
 * A question to avoid spam.
 * Shown on user registration page.
 *
 * @var string
 * @see $antispamAnswer
 */
$antispamQuestion = 'name of this application';

/**
 * The answer to the antispam question
 * Users have to write exactly this string.
 *
 * @var string
 * @see $antispamQuestion
 */
$antispamAnswer = 'semanticscuttle';

/**
 * Enable or disable user registration
 *
 * @var boolean
 */
$enableRegistration = true;



/***************************************************
 * Display Templates
 */

/**
 * Directory where the template files should be loaded from.
 * Template files are *.tpl.php
 *
 * @var string
 */
$TEMPLATES_DIR = dirname(__FILE__) . '/templates/';

/**
 * Header template file.
 * Included before content files.
 *
 * @var string
 */
$top_include = 'top.inc.php';

/**
 * Footer template file.
 * Included after content has been generated and output.
 *
 * @var string
 */
$bottom_include = 'bottom.inc.php';

/**
 * Ordering of sidebar blocks.
 * See $menu2Tags for item of menu2
 *
 * @var array
 * @see $menu2Tags
 */
$index_sidebar_blocks = array(
    'search',
    'menu2',
    'menu',
    'users',
    'recent'
);



/***************************************************
 * Bookmarks
 */

/**
 * Format for short dates.
 * Used in date() calls
 *
 * @var  string
 * @link http://php.net/date
 */
$shortdate = 'Y-m-d';

/**
 * Format of long dates.
 * Used in date() calls.
 *
 * @var string
 * @link http://php.net/date
 */
$longdate = 'j F Y';

/**
 * Include rel="nofollow" attribute on bookmark links
 *
 * @var boolean
 */
$nofollow = true;

/**
 * Default number of bookmarks per page.
 * -1 means no limit.
 *
 * @var integer
 * @see $defaultPerPageForAdmins
 */
$defaultPerPage = 10;

/**
 * Default number of bookmarks per page for admins.
 * -1 means no limit.
 *
 * @var integer
 * @see $defaultPerPage
 */
$defaultPerPageForAdmins = 10;

/**
 * Number of days that bookmarks or tags are considered "recent".
 *
 * @var integer
 */
$defaultRecentDays = 14;

/**
 * Bookmark ordering
 * (date, title, url)
 * in order ascending or descending
 * - date_desc   - By date of entry descending.
 *                 Latest entry first. (Default)
 * - date_asc    - By date of entry ascending.
 *                 Earliest entry first.
 * - title_desc  - By title, descending alphabetically.
 * - title_asc   - By title, ascending alphabetically.
 * - url_desc    - By URL, descending alphabetically.
 * - url_asc     - By URL, ascending alphabetically.
 *
 * @var string
 */
$defaultOrderBy = 'date_desc';

/**
 * Database field to use when sorting by date.
 * Options here are 'bModified' to sort after
 * modification date, and 'bDatetime' to sort
 * after creation date
 *
 * @var string
 */
$dateOrderField = 'bModified';

/**
 * What to show instead of a description if
 * a bookmark has none.
 * Default is '-'. Setting this to '' will collapse
 * the description row for bookmarks without
 * a description.
 *
 * @var string
 */
$blankDescription = '-';

/**
 * Number of entries that are shown in
 * the RSS feed by default.
 *
 * @var integer
 */
$defaultRssEntries = 15;

/**
 * Number of entries the RSS puts out
 * at maximum.
 *
 * @var integer
 */
$maxRssEntries = 100;

/**
 * Redirect all bookmarks through $url_redir to improve privacy.
 *
 * @var boolean
 * @see $url_redir
 */
$useredir = false;

/**
 * URL prefix for bookmarks to redirect through.
 *
 * @var string
 * @see $useredir
 */
$url_redir = 'http://www.google.com/url?sa=D&q=';

/**
 * Enable short URL service.
 * Can be used to visit urls using http://example.org/go/shortname
 *
 * @var boolean
 */
$shorturl = true;

/**
 * Array of bookmark extensions that Scuttle should add system tags for.
 * When adding an URL with one of the given extensions, a system
 * tag is automatically assigned.
 *
 * @var array
 */
$filetypes = array(
    'audio'    => array('mp3', 'ogg', 'wav'),
    'document' => array('doc', 'odt', 'pdf'),
    'image'    => array('gif', 'jpeg', 'jpg', 'png'),
    'video'    => array('avi', 'mov', 'mp4', 'mpeg', 'mpg', 'wmv')
);

/**
 * Link protocols that are allowed for newly added bookmarks.
 * This prevents i.e. adding javascript: links.
 *
 * @link http://en.wikipedia.org/wiki/URI_scheme
 *
 * @var array
 */
$allowedProtocols = array(
    'ftp', 'ftps',
    'http', 'https',
    'mailto', 'nntp',
    'xmpp'
);

/**
 * Enable the "common bookmark description" functionality
 *
 * @var boolean
 */
$enableCommonBookmarkDescription = true;

/**
 * Enable bookmark voting system
 *
 * @var boolean
 */
$enableVoting = true;

/**
 * Voting mode:
 * 1 - voting badge
 * 2 - voting links: hand up/down
 *
 * @var integer
 */
$votingMode = 2;

/**
 * Hide bookmarks below a certain voting from all users.
 * Null to deactivate it.
 *
 * @var integer
 */
$hideBelowVoting = null;

/**
 * Default privacy setting for bookmarks:
 * 0 - Public
 * 1 - Shared with Watchlist
 * 2 - Private
 *
 * @var integer
 */
$defaults['privacy'] = 0;


/****************************
 * Website Thumbnails
 */

/**
 * Which thumbnail service type to use.
 *
 * Currently supported:
 * - null (no screenshots)
 * - 'phancap', see http://cweiske.de/phancap.htm
 *
 * @var string
 */
$thumbnailsType = null;

/**
 * Configuration for thumbnail service.
 *
 * Phancap requires an array with the following keys:
 * - url: URL to phancap's get.php file
 * - token: user name (if access protected)
 * - secret: password for the user (if access protected)
 *
 * @var array
 */
$thumbnailsConfig = array();



/****************************
 * Tags
 */

/**
 * Enable common tag descriptions
 *
 * @var boolean
 */
$enableCommonTagDescription = true;

/**
 * If everybody may edit common tag description.
 * When set to false, only admins can do it.
 *
 * @var boolean
 */
$enableCommonTagDescriptionEditedByAll = true;

/**
 * Name of the tag whose subtags will appear in the menu box.
 *
 * @var string
 * @see $maxSizeMenuBlock
 */
$menuTag = 'menu';

/**
 * Maximum number of items (tags) appearing in menu box.
 *
 * @var integer
 * @see $menuTag
 */
$maxSizeMenuBlock = 7;

/**
 * List of tags used by menu2 sidebar box
 * Empty list = hidden menu2 box
 * menu2 displays linked tags just belonging to admins.
 *
 * @var array
 */
$menu2Tags = array(
    'menu2', 'tags', 'configurable', 'in', 'data/config.php'
);



/****************************
 * Search
 */

/**
 * Number of users' searches that are saved.
 * 10 is default, -1 means unlimited.
 *
 * @var integer
 */
$sizeSearchHistory = 10;

/**
 * Enable Google Search Engine into "gsearch/" folder.
 *
 * @var boolean
 */
$enableGoogleCustomSearch = false;




/****************************
 * Other
 */

/**
 * Enables special colors on admin pages and bookmarks.
 * Colors mark the difference to normal users.
 *
 * @var boolean
 */
$enableAdminColors = true;

/**
 * FIXME: explain better
 *
 * Add a possible anchor (structured content) for bookmarks description field
 * a simple value "xxx" (like "author") automatically associates xxx with
 * [xxx][/xxx].
 * A complex value "xxx"=>"yyy" (like "address") directly
 * associates xxx with yyy. 
 *
 * @var array
 */
$descriptionAnchors = array(
    'author',
    'isbn',
    'address' => '[address][street][/street][city][/city][/address]'
);

/**
 * GoogleAnalytics tracking code.
 * Empty string disables analytics.
 *
 * @var  string
 * @link https://www.google.com/analytics/
 */
$googleAnalyticsCode = null;




/****************************
 * avahi export script
 */

/**
 * Location of avahi service files,
 * often /etc/avahi/services/
 *
 * @var string
 */
$avahiServiceFilePath = '/etc/avahi/services/';

/**
 * File name prefix of SemanticScuttle-generated
 * service files
 *
 * @var string
 */
$avahiServiceFilePrefix = 'semanticscuttle-';

/**
 * Name of tag that bookmarks need to have to
 * get exported into avahi service files.
 *
 * @var string
 */
$avahiTagName = 'zeroconf';



/**
 * Array of key value pairs to override service class names.
 * Key is the old service name ("User"), value the new class
 * name.
 *
 * @var array
 */
$serviceoverrides = array();




/****************************
 * External user authentication
 */

/**
 * Type of external authentication via PEAR Auth
 * To use this, you also need to set
 * $serviceoverrides['User'] = 'SemanticScuttle_Service_AuthUser';
 *
 * @link http://pear.php.net/manual/en/package.authentication.auth.intro-storage.php
 *
 * @var string
 */
$authType = null;

/**
 * Options for external authentication via PEAR Auth
 *
 * @link http://pear.php.net/manual/en/package.authentication.auth.intro.php
 *
 * @var array
 */
$authOptions = null;

/**
 * Enable debugging for PEAR Authentication
 *
 * @var boolean
 */
$authDebug = false;

/**
 * Optional prefix to create email addresses from user names.
 * i.e. "@example.org" to create "user@example.org" email address
 * from "user" username.
 *
 * @var string
 */
$authEmailSuffix = null;




/**
 * URL unittests are being run against
 * Has to have a trailing slash
 *
 * @var string
 */
$unittestUrl = null;

/**
 * Allow "unittestMode=1" in URLs.
 * Should only be enabled on development systems
 *
 * @var boolean
 */
$allowUnittestMode = false;

/**
 * bookmark-bot email address mapping
 * Input address as key, user email as target
 */
$botMailMap = array();

?>
