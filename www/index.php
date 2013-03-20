<?php
/***************************************************************************
 Copyright (C) 2004 - 2006 Scuttle project
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

/* Service creation: only useful services are created */
$bookmarkservice =SemanticScuttle_Service_Factory::get('Bookmark');
$cacheservice =SemanticScuttle_Service_Factory::get('Cache');

/* Managing all possible inputs */
isset($_GET['action']) ? define('GET_ACTION', $_GET['action']): define('GET_ACTION', '');
isset($_GET['page']) ? define('GET_PAGE', $_GET['page']): define('GET_PAGE', 0);
isset($_GET['sort']) ? define('GET_SORT', $_GET['sort']): define('GET_SORT', '');


// Logout action
if (GET_ACTION == "logout") {
	$userservice->logout();
	$tplVars['currentUser'] = null;
	$tplvars['msg'] = T_('You have now logged out');
}


// Header variables
$tplVars['loadjs'] = true;
$tplVars['rsschannels'] = array(
    array(
        sprintf(T_('%s: Recent bookmarks'), $sitename),
        createURL('rss') . '?sort=' . getSortOrder()
    )
);

if ($userservice->isLoggedOn()) {
    if ($userservice->isPrivateKeyValid($currentUser->getPrivateKey())) {
        $currentUsername = $currentUser->getUsername();
        array_push(
            $tplVars['rsschannels'],
            array(
                sprintf(
                    T_('%s: Recent bookmarks (+private %s)'),
                    $sitename, $currentUsername
                ),
                createURL('rss')
                . '?sort=' . getSortOrder()
                . '&privateKey=' . $currentUser->getPrivateKey()
            )
        );
    }
}

if ($usecache) {
	// Generate hash for caching on
	$hashtext = $_SERVER['REQUEST_URI'];
	if ($userservice->isLoggedOn()) {
		$hashtext .= $userservice->getCurrentUserID();
	}
	$hash = md5($hashtext);

	// Cache for 15 minutes
	$cacheservice->Start($hash, 900);
}

// Pagination
$perpage = getPerPageCount($currentUser);
if (intval(GET_PAGE) > 1) {
	$page = intval(GET_PAGE);
	$start = ($page - 1) * $perpage;
} else {
	$page = 0;
	$start = 0;
}

$tplVars['page']     = $page;
$tplVars['start']    = $start;
$tplVars['popCount'] = 30;
$tplVars['sidebar_blocks'] = $GLOBALS["index_sidebar_blocks"];
$tplVars['range']     = 'all';
$tplVars['pagetitle'] = T_('Store, share and tag your favourite links');
$tplVars['subtitle']  = T_('All Bookmarks');
$tplVars['bookmarkCount'] = $start + 1;

$bookmarks = $bookmarkservice->getBookmarks($start, $perpage, NULL, NULL, NULL, getSortOrder(), NULL, 0, NULL);

$tplVars['total'] = $bookmarks['total'];
$tplVars['bookmarks'] = $bookmarks['bookmarks'];
$tplVars['cat_url'] = createURL('bookmarks', '%1$s/%2$s');
$tplVars['nav_url'] = createURL('index', '%3$s');
$tplVars['summarizeLinkedTags'] = true;
$tplVars['pageName'] = PAGE_INDEX;
$tplVars['user'] = '';
$tplVars['currenttag'] = '';

$templateservice->loadTemplate('bookmarks.tpl', $tplVars);

if ($usecache) {
	// Cache output if existing copy has expired
	$cacheservice->End($hash);
}
?>
