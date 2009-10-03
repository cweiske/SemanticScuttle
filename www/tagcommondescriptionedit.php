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

require_once('header.inc.php');

/* Service creation: only useful services are created */
$b2tservice       = & ServiceFactory :: getServiceInstance('Bookmark2TagService');
$cdservice        = & ServiceFactory :: getServiceInstance('CommonDescriptionService');

/* Managing all possible inputs */
isset($_POST['confirm']) ? define('POST_CONFIRM', $_POST['confirm']): define('POST_CONFIRM', '');
isset($_POST['cancel']) ? define('POST_CANCEL', $_POST['cancel']): define('POST_CANCEL', '');
isset($_POST['description']) ? define('POST_DESCRIPTION', $_POST['description']): define('POST_DESCRIPTION', '');
isset($_POST['referrer']) ? define('POST_REFERRER', $_POST['referrer']): define('POST_REFERRER', '');


/* Managing current logged user */
$currentUser = $userservice->getCurrentObjectUser();

/* Managing path info */
list ($url, $tag) = explode('/', $_SERVER['PATH_INFO']);

//permissions
if(!$userservice->isLoggedOn() || (!$GLOBALS['enableCommonTagDescriptionEditedByAll'] && !$currentUser->isAdmin())) {
	$tplVars['error'] = T_('Permission denied.');
	$templateservice->loadTemplate('error.500.tpl', $tplVars);
	exit();
}

$template   = 'tagcommondescriptionedit.tpl';

if (POST_CONFIRM) {

	if ( strlen($tag)>0 &&
	$cdservice->addTagDescription($tag, stripslashes(POST_DESCRIPTION), $currentUser->getId(), time())
	) {
		$tplVars['msg'] = T_('Tag common description updated');
		header('Location: '. POST_REFERRER);
	} else {
		$tplVars['error'] = T_('Failed to update the tag common description');
		$template         = 'error.500.tpl';
	}
} elseif (POST_CANCEL) {
	header('Location: '. POST_REFERRER);
} else {
	$tplVars['subtitle']    = T_('Edit Tag Common Description') .': '. $tag;
	$tplVars['formaction']  = $_SERVER['SCRIPT_NAME'] .'/'. $tag;
	$tplVars['referrer']    = $_SERVER['HTTP_REFERER'];
	$tplVars['tag']         = $tag;
	$tplVars['description'] = $cdservice->getLastTagDescription($tag);
}
$templateservice->loadTemplate($template, $tplVars);
?>
