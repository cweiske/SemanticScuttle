<?php
/***************************************************************************
 Copyright (C) 2005 - 2006 Scuttle project
 http://sourceforge.net/projects/scuttle/
 http://scuttle.org/

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 ***************************************************************************/

require_once 'www-header.php';

/* Managing all possible inputs */
isset($_POST['terms']) ? define('POST_TERMS', $_POST['terms']): define('POST_TERMS', '');
isset($_POST['range']) ? define('POST_RANGE', $_POST['range']): define('POST_RANGE', '');
isset($_GET['page']) ? define('GET_PAGE', $_GET['page']): define('GET_PAGE', 0);
isset($_GET['sort']) ? define('GET_SORT', $_GET['sort']): define('GET_SORT', '');


// POST
if (POST_TERMS != '') {
	// Redirect to GET
	header(
        'Location: '
        . createURL('search', POST_RANGE .'/'. filter(POST_TERMS, 'url'))
    );
    exit();

}

/* Service creation: only useful services are created */
$bookmarkservice = SemanticScuttle_Service_Factory::get('Bookmark');
$searchhistoryservice = SemanticScuttle_Service_Factory::get('SearchHistory');

/* Managing current logged user */
$currentUserId = $userservice->getCurrentUserId();


$exploded = isset($_SERVER['PATH_INFO'])
    ? explode('/', $_SERVER['PATH_INFO']) : null;
if(count($exploded) == 4) {
    list($url, $range, $terms, $page) = $exploded;
} else if (count($exploded) == 2) {
    list($url, $range) = $exploded;
    $terms = $page = NULL;
} else {
    list($url, $range, $terms) = $exploded;
    $page = NULL;
}
//some OpenSearch clients need that
$terms = urldecode($terms);

$tplVars['loadjs'] = true;

// Pagination
$perpage = getPerPageCount($currentUser);
if (intval(GET_PAGE) > 1) {
    $page = intval(GET_PAGE);
    $start = ($page - 1) * $perpage;
} else {
    $page = 0;
    $start = 0;
}

$s_user = NULL;
$s_start = NULL;
$s_end = NULL;
$s_watchlist = NULL;

// No search terms
if (is_null($terms)) {
    $tplVars['subtitle'] = T_('Search Bookmarks');
    $s_end = date('Y-m-d H:i:s', strtotime('tomorrow'));
    $s_start = date('Y-m-d H:i:s', strtotime($s_end .' -'. $defaultRecentDays .' days'));

    // Search terms
} else {
    $tplVars['subtitle'] = T_('Search Results');
    $selected = ' selected="selected"';

    switch ($range) {
    case 'all':
        $tplVars['select_all'] = $selected;
        $s_user = NULL;
        break;
    case 'watchlist':
        $tplVars['select_watchlist'] = $selected;
        $s_user = $currentUserId;
        $s_watchlist = true;
        break;
    default:
        $s_user = $range;
        break;
    }

    if (isset($s_user)) {
        $tplVars['user'] = $range;
        $s_user = $userservice->getIdFromUser($s_user);
        if($s_user == NULL) {
            $tplVars['error'] = sprintf(T_('User with username %s was not found'), $s_user);
            $templateservice->loadTemplate('error.404.tpl', $tplVars);
            exit();
        }

    }
}
$bookmarks = $bookmarkservice->getBookmarks(
    $start, $perpage, $s_user, NULL, $terms, getSortOrder(),
    $s_watchlist, $s_start, $s_end
);

// Save search
$searchhistoryservice->addSearch(
    $terms, $range, $bookmarks['total'], $currentUserId
);

if (isset($_GET['lucky']) && $_GET['lucky']
    && isset($bookmarks['bookmarks'][0])
) {
    $url = $bookmarks['bookmarks'][0]['bAddress'];
    header('Location: ' . $url);
    exit();
}

if ($GLOBALS['enableGoogleCustomSearch']) {
    $tplVars['tipMsg'] = T_('Unsatisfied? You can also try our ')
        . '<a href="' . createUrl('gsearch/index') . '">Google Custom Search page</a>.';
}
$tplVars['rsschannels'] = array();
$tplVars['page'] = $page;
$tplVars['start'] = $start;
$tplVars['popCount'] = 25;
$tplVars['sidebar_blocks'] = array('search', 'recent', 'menu2');
$tplVars['range'] = $range;
$tplVars['terms'] = $terms;
$tplVars['pagetitle'] = T_('Search Bookmarks');
$tplVars['bookmarkCount'] = $start + 1;
$tplVars['total'] = $bookmarks['total'];
$tplVars['bookmarks'] = $bookmarks['bookmarks'];
$tplVars['cat_url'] = createURL('tags', '%2$s');
$tplVars['nav_url'] = createURL('search', $range .'/'. $terms .'/%3$s');

$templateservice->loadTemplate('bookmarks.tpl', $tplVars);
?>
