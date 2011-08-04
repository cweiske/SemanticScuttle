<?php
/***************************************************************************
 Copyright (C) 2006 - 2007 Scuttle project
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
$b2tservice       = SemanticScuttle_Service_Factory :: get('Bookmark2Tag');
$tagservice   = SemanticScuttle_Service_Factory :: get('Tag');
$tag2tagservice   = SemanticScuttle_Service_Factory :: get('Tag2Tag');

/* Managing all possible inputs */
isset($_POST['confirm']) ? define('POST_CONFIRM', $_POST['confirm']): define('POST_CONFIRM', '');
isset($_POST['cancel']) ? define('POST_CANCEL', $_POST['cancel']): define('POST_CANCEL', '');
isset($_POST['old']) ? define('POST_OLD', $_POST['old']): define('POST_OLD', '');
isset($_POST['new']) ? define('POST_NEW', $_POST['new']): define('POST_NEW', '');

/* Managing current logged user */
$currentUser = $userservice->getCurrentObjectUser();

/* Managing path info */
list ($url, $tag) = explode('/', $_SERVER['PATH_INFO']);
//$tag        = isset($_GET['query']) ? $_GET['query'] : NULL;
$template   = 'tagrename.tpl';

if (POST_CONFIRM) {
	if (trim(POST_OLD) != '') {
		$old = trim(POST_OLD);
	} else {
		$old = NULL;
	}

	if (trim(POST_NEW) != '') {
		$new = trim(POST_NEW);
	} else {
		$new = NULL;
	}

	if (
	!is_null($old) &&
	!is_null($new) &&
	$tagservice->renameTag($currentUser->getId(), $old, $new) &&
	$b2tservice->renameTag($currentUser->getId(), $old, $new) &&
	$tag2tagservice->renameTag($currentUser->getId(), $old, $new)
	) {
		$tplVars['msg'] = T_('Tag renamed');
		header('Location: '. createURL('bookmarks', $currentUser->getUsername()));
	} else {
		$tplVars['error'] = T_('Failed to rename the tag');
		$template         = 'error.500.tpl';
	}
} elseif (POST_CANCEL) {
	header('Location: '. createURL('bookmarks', $currentUser->getUsername() .'/'. $tag));
} else {
	$tplVars['subtitle']    = T_('Rename Tag') .': '. $tag;
	$tplVars['formaction']  = $_SERVER['SCRIPT_NAME'] .'/'. $tag;
	$tplVars['referrer']    = $_SERVER['HTTP_REFERER'];
	$tplVars['old']         = $tag;
}
$templateservice->loadTemplate($template, $tplVars);
?>
