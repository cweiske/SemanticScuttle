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

require_once('header.inc.php');

/* Service creation: only useful services are created */
$userservice =& ServiceFactory::getServiceInstance('UserService');

/* Managing current logged user */
$currentUser = $userservice->getCurrentObjectUser();

/* Managing path info */
@list($url, $user) = isset($_SERVER['PATH_INFO']) ? explode('/', $_SERVER['PATH_INFO']) : NULL;


if ($userservice->isLoggedOn() && $user) {
	$tplVars = array();
	$pagetitle = '';

	if (is_int($user)) {
		$userid = intval($user);
	} else {
		$userinfo = $userservice->getObjectUserByUsername($user);
		if ($userinfo == '') {
			$tplVars['error'] = sprintf(T_('User with username %s was not found'), $user);
			$templateservice->loadTemplate('error.404.tpl', $tplVars);
			exit();
		} else {
			$userid =& $userinfo->getId();
		}
	}

	$watched = $userservice->getWatchStatus($userid, $currentUser->getId());
	$changed = $userservice->setWatchStatus($userid);

	if ($watched) {
		$tplVars['msg'] = T_('User removed from your watchlist');
	} else {
		$tplVars['msg'] = T_('User added to your watchlist');
	}

	header('Location: '. createURL('watchlist', $currentUser->getUsername()));
}
?>
