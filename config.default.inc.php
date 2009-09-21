<?php
/**
 * Default configuration file for SemanticScuttle
 *
 * This file is included just before config.inc.php
 * If there is something you want to change, copy the lines
 * in your personal config.inc.php file
 *
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 *
 * @link http://sourceforge.net/projects/semanticscuttle/
 */


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



/***************************************************
 * System configuration
 */


/**
 * SemanticScuttle root directory.
 *
 * Set to NULL to autodetect the root url of the website.
 *
 * If your installation is in a subdirectory like
 * "http://www.example.com/semanticscuttle/" then
 * replace NULL by your address (between "" and with trailing '/')
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
$dbhost = 'localhost';

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
$shortdate = 'd-m-Y';

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
 * Enable the "common bookmark description" functionality
 *
 * @var boolean
 */
$enableCommonBookmarkDescription = true;



/****************************
 * Website Thumbnails
 */

/**
 * Enable bookmark website thumbnails.
 *
 * According to artviper.net license, buy a license if you
 * gain profit with your pages.
 *
 * @var  boolean
 * @link http://www.websitethumbnail.de/
 */
$enableWebsiteThumbnails = false;

/**
 * User ID from websitethumbnail.de
 *
 * You need to register on
 *  http://www.artviper.net/registerAPI.php
 * in order to use thumbnails on your domain
 *
 * @var  string
 * @link http://www.artviper.net/registerAPI.php
 */
$thumbnailsUserId = null;

/**
 * API key.
 * Sent to you by artviper.net after registration.
 *
 * @var string
 */
$thumbnailsKey = null;



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
    'menu2', 'tags', 'configurable', 'in', 'configincphp'
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

?>
