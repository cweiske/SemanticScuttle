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

/* Managing current logged user */
$currentUser = $userservice->getCurrentObjectUser();

$pagetitle = T_('Users');

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
$tplVars['pagetitle'] = $pagetitle;
$tplVars['loadjs'] = true;

$tplVars['sidebar_blocks'] = array('recent', 'popular');
$tplVars['subtitle'] = filter($pagetitle);

$tplVars['users'] =& $userservice->getUsers();
//$tplVars['cat_url'] = createURL('tags', '%2$s');
//$tplVars['nav_url'] = createURL('tags', '%2$s%3$s');

$templateservice->loadTemplate('users.tpl', $tplVars);

if ($usecache) {
	// Cache output if existing copy has expired
	$cacheservice->End($hash);
}
?>
