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

require_once 'www-header.php';

/* Service creation: only useful services are created */
$bookmark2tagservice = SemanticScuttle_Service_Factory::get('Bookmark2Tag');
$bookmarkservice = SemanticScuttle_Service_Factory::get('Bookmark');
$tag2tagservice = SemanticScuttle_Service_Factory::get('Tag2Tag');
$tagcacheservice = SemanticScuttle_Service_Factory::get('TagCache');
$commondescriptionservice = SemanticScuttle_Service_Factory::get('CommonDescription');
$searchhistoryservice = SemanticScuttle_Service_Factory::get('SearchHistory');
$tagstatservice = SemanticScuttle_Service_Factory::get('TagStat');

// Header variables
$tplVars['subtitle'] = T_('Manage users');
$tplVars['loadjs'] = true;
$tplVars['sidebar_blocks'] = array('users' );
$tplVars['error'] = '';
$tplVars['msg'] = '';

if ( !$userservice->isLoggedOn() ) {
	header('Location: '. createURL('login', ''));
	exit();
}

if ( !$currentUser->isAdmin() ) {
	header('Location: '. createURL('bookmarks', $currentUser->getUsername()));
	exit();
}

@list($url, $action, $user) = isset($_SERVER['PATH_INFO']) ? explode('/', $_SERVER['PATH_INFO']) : NULL;

if ($action
    && (strpos($_SERVER['HTTP_REFERER'], ROOT.'admin') <= 6)
    // Prevent CSRF attacks. 6 is needed for "//example.org"-root urls
) {
	switch ( $action ) {
		case 'delete':
			if ( $user && ($userinfo = $userservice->getUserByUsername($user)) ) {
				$uId = $userinfo['uId'];

				$tagcacheservice->deleteByUser($uId);
				$tag2tagservice->removeLinkedTagsForUser($uId);
				$userservice->deleteUser($uId);
				$bookmark2tagservice->deleteTagsForUser($uId);
				$commondescriptionservice->deleteDescriptionsForUser($uId);
				$searchhistoryservice->deleteSearchHistoryForUser($uId);
				$tagstatservice->deleteTagStatForUser($uId);				
				// XXX: don't delete bookmarks before tags, else tags can't be deleted !!!
				$bookmarkservice->deleteBookmarksForUser($uId);

				$tplVars['msg'] = sprintf(T_('%s and all his bookmarks and tags were deleted.'), $user);
			}
			break;
		case 'checkUrl' :
			$bookmarks = $bookmarkservice->getBookmarks(0, NULL, NULL, NULL, NULL, getSortOrder());
			foreach($bookmarks['bookmarks'] as $bookmark) {
				if(!checkUrl($bookmark['bAddress'])) {
					$tplVars['error'].= T_('Problem with ').$bookmark['bAddress'].' ('. $bookmark['username'] .')<br/>';  
				}
			}
			break;
		default:
			// DO NOTHING
	}
}

$templatename = 'admin.tpl';
$users = $userservice->getObjectUsers();

if ( !is_array($users) ) {
	$users = array();
}

$tplVars['users'] = $users;

$templateservice->loadTemplate($templatename, $tplVars);
?>
