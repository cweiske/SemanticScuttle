<?php
/**
 * RSS output of the latest posts.
 *
 * Parameter:
 * - count=15
 *   Sets the number of RSS entries to export
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

$httpContentType = 'application/rss+xml';
require_once 'www-header.php';

/* Service creation: only useful services are created */
$bookmarkservice = SemanticScuttle_Service_Factory::get('Bookmark');
$cacheservice    = SemanticScuttle_Service_Factory::get('Cache');

if (isset($_SERVER['PATH_INFO']) && strlen($_SERVER['PATH_INFO']) > 1) {
    $parts = explode('/', $_SERVER['PATH_INFO']);
    if (count($parts) == 3) {
        list($url, $user, $cat) = $parts;
    } else {
        list($url, $user) = $parts;
        $cat = null;
    }
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

if (isset($_GET['count'])) {
    $rssEntries = (int)$_GET['count'];
}
if (!isset($rssEntries) || $rssEntries <= 0) {
    $rssEntries = $defaultRssEntries;
} else if ($rssEntries > $maxRssEntries) {
    $rssEntries = $maxRssEntries;
}

$privateKey = null;
if (isset($_GET['privateKey'])) {
    $privateKey = $_GET['privateKey'];
}

$userid    = null;
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
            $userid = $userinfo[$userservice->getFieldName('primary')];
            /* if user is not logged in and has valid privateKey */
            if (!$userservice->isLoggedOn()) {
                if ($privateKey != null) {
                    if (!$userservice->loginPrivateKey($privateKey)) {
                        $tplVars['error'] = sprintf(T_('Failed to Autenticate User with username %s using private key'), $user);
                        header('Content-type: text/html; charset=utf-8');
                        $templateservice->loadTemplate('error.404.tpl', $tplVars);
                        //throw a 404 error
                        exit();
                    }
                }
            }

        } else {
            $tplVars['error'] = sprintf(T_('User with username %s was not found'), $user);
            header('Content-type: text/html; charset=utf-8');
            $templateservice->loadTemplate('error.404.tpl', $tplVars);
            //throw a 404 error
            exit();
        }
    }
    $pagetitle .= ": ". $user;
} else {
    if ($privateKey != null) {
        if (!$userservice->loginPrivateKey($privateKey)) {
            $tplVars['error'] = sprintf(T_('Failed to Autenticate User with username %s using private key'), $user);
            header('Content-type: text/html; charset=utf-8');
            $templateservice->loadTemplate('error.404.tpl', $tplVars);
            //throw a 404 error
            exit();
        }
    } else {
        $userid = null;
    }
}

if ($cat) {
    $pagetitle .= ": ". str_replace('+', ' + ', $cat);
}

$tplVars['feedtitle'] = filter($GLOBALS['sitename'] . (isset($pagetitle) ? $pagetitle : ''));
$tplVars['pagelink'] = addProtocolToUrl(ROOT);
$tplVars['feedlink'] = addProtocolToUrl(ROOT) . 'rss?sort=' . getSortOrder();
$tplVars['feeddescription'] = sprintf(T_('Recent bookmarks posted to %s'), $GLOBALS['sitename']);

$bookmarks = $bookmarkservice->getBookmarks(
    0, $rssEntries, $userid, $cat,
    null, getSortOrder(), $watchlist,
    null, null, null
);

$bookmarks_tmp = filter($bookmarks['bookmarks']);

$bookmarks_tpl = array();
$latestdate    = null;
$guidBaseUrl   = addProtocolToUrl(ROOT) . '#';
foreach ($bookmarks_tmp as $key => $row) {
    $_link = $row['bAddress'];
    // Redirection option
    if ($GLOBALS['useredir']) {
        $_link = $GLOBALS['url_redir'] . $_link;
    }
    if ($row['bDatetime'] > $latestdate) {
        $latestdate = $row['bDatetime'];
    }
    $_pubdate = gmdate('r', strtotime($row['bDatetime']));

    $bookmarks_tpl[] = array(
        'title'       => $row['bTitle'],
        'link'        => $_link,
        'description' => $row['bDescription'],
        'creator'     => SemanticScuttle_Model_UserArray::getName($row),
        'pubdate'     => $_pubdate,
        'tags'        => $row['tags'],
        'guid'        => $guidBaseUrl . $row['bId'],
    );
}
unset($bookmarks_tmp);
unset($bookmarks);
$tplVars['bookmarks']      = $bookmarks_tpl;
$tplVars['feedlastupdate'] = date('r', strtotime($latestdate));

$templateservice->loadTemplate('rss.tpl', $tplVars);

if ($usecache) {
    // Cache output if existing copy has expired
    $cacheservice->End($hash);
}
?>
