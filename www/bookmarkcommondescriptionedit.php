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
$bookmarkservice  = SemanticScuttle_Service_Factory :: get('Bookmark');
$cdservice        = SemanticScuttle_Service_Factory :: get('CommonDescription');

/* Managing all possible inputs */
isset($_POST['confirm']) ? define('POST_CONFIRM', $_POST['confirm']): define('POST_CONFIRM', '');
isset($_POST['cancel']) ? define('POST_CANCEL', $_POST['cancel']): define('POST_CANCEL', '');
isset($_POST['hash']) ? define('POST_HASH', $_POST['hash']): define('POST_HASH', '');
isset($_POST['title']) ? define('POST_TITLE', $_POST['title']): define('POST_TITLE', '');
isset($_POST['description']) ? define('POST_DESCRIPTION', $_POST['description']): define('POST_DESCRIPTION', '');

// prevent cycle between personal and common edit page
if(!isset($_POST['referrer'])) {
	define('POST_REFERRER', '');
} elseif(strpos($_POST['referrer'], ROOT.'edit.php') == 0) {
	define('POST_REFERRER', createUrl('history', POST_HASH));
} else {
	define('POST_REFERRER', $_POST['referrer']);
}


list ($url, $hash) = explode('/', $_SERVER['PATH_INFO']);
$template   = 'bookmarkcommondescriptionedit.tpl';


//permissions
if(is_null($currentUser)) {
	$tplVars['error'] = T_('Permission denied.');
	$templateservice->loadTemplate('error.500.tpl', $tplVars);
	exit();
}

if (POST_CONFIRM) {
	if (strlen($hash)>0 &&
	$cdservice->addBookmarkDescription(POST_HASH, stripslashes(POST_TITLE), stripslashes(POST_DESCRIPTION), $currentUser->getId(), time())
	) {
		$tplVars['msg'] = T_('Bookmark common description updated');
		header('Location: '. POST_REFERRER);
	} else {
		$tplVars['error'] = T_('Failed to update the bookmark common description');
		$template         = 'error.500.tpl';
	}
} elseif (POST_CANCEL) {
	header('Location: '. POST_REFERRER);
} else {
	$bkm = $bookmarkservice->getBookmarkByHash($hash);

	$tplVars['subtitle']    = T_('Edit Bookmark Common Description') .': '. $bkm['bAddress'];
	$tplVars['formaction']  = $_SERVER['SCRIPT_NAME'] .'/'. $hash;
	$tplVars['referrer']    = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
	$tplVars['hash']        = $hash;
	$tplVars['description'] = $cdservice->getLastBookmarkDescription($hash);
}
$templateservice->loadTemplate($template, $tplVars);
?>
