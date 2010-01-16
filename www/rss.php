<?php
/**
 * RSS output of the latest posts.
 *
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

require_once '../src/SemanticScuttle/header.php';

/* Service creation: only useful services are created */
$bookmarkservice = SemanticScuttle_Service_Factory::get('Bookmark');
$cacheservice    = SemanticScuttle_Service_Factory::get('Cache');

header('Content-Type: application/xml');
if (isset($_SERVER['PATH_INFO']) && strlen($_SERVER['PATH_INFO']) >1) {
    list($url, $user, $cat) = explode('/', $_SERVER['PATH_INFO']);
} else {
    $url = '';
    $user = '';
    $cat = null;
}

if ($usecache) {
    // Generate hash for caching on
    $hashtext = $_SERVER['REQUEST_URI'];
    if ($userservice->isLoggedOn()) {
        $hashtext .= $userservice->getCurrentUserID();
        if ($currentUser->getUsername() == $user) {
            $hashtext .= $user;
        }
    }
    $hash = md5($hashtext);

    // Cache for an hour
    $cacheservice->Start($hash, 3600);
}

$watchlist = null;
$pagetitle = '';
if ($user && $user != 'all') {
    if ($user == 'watchlist') {
        $user = $cat;
        $cat = null;
        $watchlist = true;
    }
    if (is_int($user)) {
        $userid = intval($user);
    } else {
        if ($userinfo = $userservice->getUserByUsername($user)) {
            $userid =& $userinfo[$userservice->getFieldName('primary')];
        } else {
            $tplVars['error'] = sprintf(T_('User with username %s was not found'), $user);
            $templateservice->loadTemplate('error.404.tpl', $tplVars);
            //throw a 404 error
            exit();
        }
    }
    $pagetitle .= ": ". $user;
} else {
    $userid = null;
}

if ($cat) {
    $pagetitle .= ": ". str_replace('+', ' + ', $cat);
}

$tplVars['feedtitle'] = filter($GLOBALS['sitename'] . (isset($pagetitle) ? $pagetitle : ''));
$tplVars['feedlink'] = ROOT;
$tplVars['feeddescription'] = sprintf(T_('Recent bookmarks posted to %s'), $GLOBALS['sitename']);

$bookmarks =& $bookmarkservice->getBookmarks(0, 15, $userid, $cat, null, getSortOrder(), $watchlist);

$bookmarks_tmp =& filter($bookmarks['bookmarks']);

$bookmarks_tpl = array();
foreach (array_keys($bookmarks_tmp) as $key) {
    $row =& $bookmarks_tmp[$key];

    $_link = $row['bAddress'];
    // Redirection option
    if ($GLOBALS['useredir']) {
        $_link = $GLOBALS['url_redir'] . $_link;
    }
    $_pubdate = gmdate("r", strtotime($row['bDatetime']));
    // array_walk($row['tags'], 'filter');

    $bookmarks_tpl[] = array(
        'title' => $row['bTitle'],
        'link'  => $_link,
        'description' => $row['bDescription'],
        'creator' => $row['username'],
        'pubdate' => $_pubdate,
        'tags' => $row['tags']
    );
}
unset($bookmarks_tmp);
unset($bookmarks);
$tplVars['bookmarks'] =& $bookmarks_tpl;

$templateservice->loadTemplate('rss.tpl', $tplVars);

if ($usecache) {
    // Cache output if existing copy has expired
    $cacheservice->End($hash);
}
?>
