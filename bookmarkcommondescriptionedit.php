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
$bookmarkservice  = & ServiceFactory :: getServiceInstance('BookmarkService');
$tagservice       = & ServiceFactory :: getServiceInstance('TagService');
$templateservice  = & ServiceFactory :: getServiceInstance('TemplateService');
$userservice      = & ServiceFactory :: getServiceInstance('UserService');
$cdservice        = & ServiceFactory :: getServiceInstance('CommonDescriptionService');

list ($url, $hash) = explode('/', $_SERVER['PATH_INFO']);
$template   = 'bookmarkcommondescriptionedit.tpl';

$logged_on_user = $userservice->getCurrentUser();

//permissions
if($logged_on_user == null) {
    $tplVars['error'] = T_('Permission denied.');
    $templateservice->loadTemplate('error.500.tpl', $tplVars);
    exit();
}

if ($_POST['confirm']) {

   if (strlen($hash)>0 &&
	$cdservice->addBookmarkDescription($_POST['hash'], stripslashes($_POST['title']), stripslashes($_POST['description']), $logged_on_user['uId'], time())
   ) {
      $tplVars['msg'] = T_('Bookmark common description updated');
      header('Location: '. $_POST['referrer']);
   } else {
      $tplVars['error'] = T_('Failed to update the bookmark common description');
      $template         = 'error.500.tpl';
   }
} elseif ($_POST['cancel']) {
    $logged_on_user = $userservice->getCurrentUser();
    header('Location: '. $_POST['referrer']);
} else {
   $bkm = $bookmarkservice->getBookmarkByHash($hash);

   $tplVars['subtitle']    = T_('Edit Bookmark Common Description') .': '. $bkm['bAddress'];
   $tplVars['formaction']  = $_SERVER['SCRIPT_NAME'] .'/'. $hash;
   $tplVars['referrer']    = $_SERVER['HTTP_REFERER'];
   $tplVars['hash']        = $hash;
   $tplVars['description'] = $cdservice->getLastBookmarkDescription($hash);
}
$templateservice->loadTemplate($template, $tplVars);
?>
