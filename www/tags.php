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
isset($_GET['page']) ? define('GET_PAGE', $_GET['page']): define('GET_PAGE', 0);
isset($_GET['sort']) ? define('GET_SORT', $_GET['sort']): define('GET_SORT', '');

/* Managing current logged user */
$currentUser = $userservice->getCurrentObjectUser();

/* Managing path info */
list($url, $cat) = explode('/', $_SERVER['PATH_INFO']);


if (!$cat) {
	header('Location: '. createURL('populartags'));
	exit;
}

$titleTags = explode('+', filter($cat));
$pagetitle = T_('Tags') .': ';
for($i = 0; $i<count($titleTags);$i++) {
	$pagetitle.= $titleTags[$i].'<a href="'.createUrl('tags', aggregateTags($titleTags, '+', $titleTags[$i])).'" title="'.T_('Remove the tag from the selection').'">*</a> + ';
}
$pagetitle = substr($pagetitle, 0, strlen($pagetitle) - strlen(' + ')); 


//$cattitle = str_replace('+', ' + ', $cat);

if ($usecache) {
	// Generate hash for caching on
	if ($userservice->isLoggedOn()) {
		$hash = md5($_SERVER['REQUEST_URI'] . $currentUser->getId());
	} else {
		$hash = md5($_SERVER['REQUEST_URI']);
	}

	// Cache for 30 minutes
	$cacheservice->Start($hash, 1800);
}

// Header variables
$tplVars['pagetitle'] = T_('Tags') .': '. $cat;
$tplVars['loadjs'] = true;
$tplVars['rsschannels'] = array(
    array(
        sprintf(T_('%s: tagged with "%s"'), $sitename, $cat),
        createURL('rss', 'all/' . filter($cat, 'url'))
        . '?sort='.getSortOrder()
    )
);

if ($userservice->isLoggedOn()) {
    if ($userservice->isPrivateKeyValid($currentUser->getPrivateKey())) {
        $currentUsername = $currentUser->getUsername();
        array_push(
            $tplVars['rsschannels'],
            array(
                sprintf(
                    T_('%s: tagged with "%s" (+private %s)'),
                    $sitename, $cat, $currentUsername
                ),
                createURL('rss', filter($currentUsername, 'url'))
                . '?sort=' . getSortOrder()
                . '&privateKey=' . $currentUser->getPrivateKey()
            )
        );
    }
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

$tplVars['page'] = $page;
$tplVars['start'] = $start;
$tplVars['popCount'] = 25;
$tplVars['currenttag'] = $cat;
$tplVars['sidebar_blocks'] = array('linked', 'related', 'menu2');//array('linked', 'related', 'popular');
$tplVars['subtitlehtml'] = $pagetitle;
$tplVars['bookmarkCount'] = $start + 1;
$bookmarks =& $bookmarkservice->getBookmarks($start, $perpage, NULL, $cat, NULL, getSortOrder());
$tplVars['total'] = $bookmarks['total'];
$tplVars['bookmarks'] =& $bookmarks['bookmarks'];
$tplVars['cat_url'] = createURL('bookmarks', '%1$s/%2$s');
$tplVars['nav_url'] = createURL('tags', '%2$s%3$s');

$templateservice->loadTemplate('bookmarks.tpl', $tplVars);

if ($usecache) {
	// Cache output if existing copy has expired
	$cacheservice->End($hash);
}

?>
