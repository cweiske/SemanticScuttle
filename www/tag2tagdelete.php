<?php
/***************************************************************************
 Copyright (C) 2006 Scuttle project
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
$tag2tagservice = SemanticScuttle_Service_Factory :: get('Tag2Tag');

/* Managing all possible inputs */
isset($_POST['confirm']) ? define('POST_CONFIRM', $_POST['confirm']): define('POST_CONFIRM', '');
isset($_POST['cancel']) ? define('POST_CANCEL', $_POST['cancel']): define('POST_CANCEL', '');
isset($_POST['tag1']) ? define('POST_TAG1', $_POST['tag1']): define('POST_TAG1', '');
isset($_POST['linkType']) ? define('POST_LINKTYPE', $_POST['linkType']): define('POST_LINKTYPE', '');
isset($_POST['tag2']) ? define('POST_TAG2', $_POST['tag2']): define('POST_TAG2', '');

isset($_SERVER['HTTP_REFERER']) ? define('HTTP_REFERER', $_SERVER['HTTP_REFERER']): define('HTTP_REFERER', '');

/* Managing current logged user */
$currentUser = $userservice->getCurrentObjectUser();

//permissions
if(!$userservice->isloggedOn()) {
	$tplVars['error'] = T_('Permission denied.');
	$templateservice->loadTemplate('error.500.tpl', $tplVars);
	exit();
}

/* Managing path info */
if(isset($_SERVER['PATH_INFO'])) {
	$exploded = explode('/', $_SERVER['PATH_INFO']);
	if(count($exploded) == 3) {
		list ($url, $tag1, $tag2) = explode('/', $_SERVER['PATH_INFO']);
	} else {
		list ($url, $tag1) = explode('/', $_SERVER['PATH_INFO']);
		$tag2 = '';
	}
} else {
	$url = $tag1 =  $tag2 = '';
}



if (POST_CONFIRM) {
	$tag = POST_TAG1;
	$linkType = POST_LINKTYPE;
	$newTag = POST_TAG2;
	if ($tag2tagservice->removeLinkedTags(POST_TAG1, POST_TAG2, POST_LINKTYPE, $currentUser->getId())) {
		$tplVars['msg'] = T_('Tag link deleted');
		header('Location: '. createURL('bookmarks', $currentUser->getUsername().'/'.$tag));
	} else {
		$tplVars['error'] = T_('Failed to delete the link');
		$templateservice->loadTemplate('error.500.tpl', $tplVars);
		exit();
	}
} elseif (POST_CANCEL) {
	header('Location: '. createURL('bookmarks', $currentUser->getUsername() .'/'. $tag1));
}

$tplVars['links']	= $tag2tagservice->getLinks($currentUser->getId());

$tplVars['tag1']	= $tag1;
$tplVars['tag2']	= $tag2;
$tplVars['subtitle']    = T_('Delete Link Between Tags') .': '. $tag1.' > '.$tag2;
$tplVars['formaction']  = $_SERVER['SCRIPT_NAME'];
$tplVars['referrer']    = HTTP_REFERER;
$templateservice->loadTemplate('tag2tagdelete.tpl', $tplVars);
?>
