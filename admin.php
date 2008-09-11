<?php
/***************************************************************************
Copyright (C) 2007 - 2008 SemanticScuttle project (fork from Scuttle)
http://sourceforge.net/projects/semanticscuttle/

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

$userservice = & ServiceFactory :: getServiceInstance('UserService');
$bookmark2tagservice = & ServiceFactory :: getServiceInstance('Bookmark2Tagservice');
$bookmarkservice = & ServiceFactory :: getServiceInstance('BookmarkService');
$tag2tagservice = & ServiceFactory :: getServiceInstance('Tag2TagService');
$templateservice = & ServiceFactory :: getServiceInstance('TemplateService');

// Header variables
$tplVars['subtitle'] = T_('Manage users');
$tplVars['loadjs'] = true;

if ( !$userservice->isLoggedOn() ) {
    header('Location: '. createURL('login', ''));
    exit();
}

$currentUser = $userservice->getCurrentUser();
$currentUserID = $userservice->getCurrentUserId();
$currentUsername = $currentUser[$userservice->getFieldName('username')];

if ( !$userservice->isAdmin($currentUserID) ) {
    header('Location: '. createURL('bookmarks', $currentUsername));
    exit();
}

@list($url, $action, $user) = isset($_SERVER['PATH_INFO']) ? explode('/', $_SERVER['PATH_INFO']) : NULL;

if ( $action ) {
	switch ( $action ) {
		case 'delete':
			if ( $user && ($userinfo = $userservice->getUserByUsername($user)) ) {
				$uId = $userinfo['uId'];

				$tag2tagservice->removeLinkedTags('','','',$uId);
				$userservice->deleteUser($uId);
				$bookmark2tagservice->deleteTagsForUser($uId);
				// XXX: don't delete bookmarks before tags, else tags can't be deleted !!!
				$bookmarkservice->deleteBookmarksForUser($uId);

				$tplVars['msg'] = sprintf(T_('%s and all his bookmarks and tags were deleted.'), $user);
			}
			break;
		default:
			// DO NOTHING
	}
}

$templatename = 'userlist.tpl';
$users =& $userservice->getAllUsers();

if ( !is_array($users) ) {
	$users = array();
}

$tplVars['users'] =& $users;

$templateservice->loadTemplate($templatename, $tplVars);
?>
