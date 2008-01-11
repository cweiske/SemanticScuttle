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

require_once('header.inc.php');
$tag2tagservice = & ServiceFactory :: getServiceInstance('Tag2TagService');
$templateservice = & ServiceFactory :: getServiceInstance('TemplateService');
$userservice = & ServiceFactory :: getServiceInstance('UserService');

$logged_on_user = $userservice->getCurrentUser();

//permissions
if($logged_on_user == null) {
    $tplVars['error'] = T_('Permission denied.');
    $templateservice->loadTemplate('error.500.tpl', $tplVars);
    exit();
}


list ($url, $tag) = explode('/', $_SERVER['PATH_INFO']);

if ($_POST['confirm']) {
    $newTag = $_POST['newTag'];
    if ($tag2tagservice->addLinkedTags($tag, $newTag, '>', $userservice->getCurrentUserId())) {
        $tplVars['msg'] = T_('Tag link created');
        header('Location: '. createURL('bookmarks', $logged_on_user[$userservice->getFieldName('username')]));
    } else {
        $tplVars['error'] = T_('Failed to create the link');
        $templateservice->loadTemplate('error.500.tpl', $tplVars);
        exit();
    }
} elseif ($_POST['cancel']) {
    header('Location: '. createURL('bookmarks', $logged_on_user[$userservice->getFieldName('username')] .'/'. $tags));
}

$tplVars['tag']		= $tag;
$tplVars['subtitle']    = T_('Add Tag Link') .': '. $tag;
$tplVars['formaction']  = $_SERVER['SCRIPT_NAME'] .'/'. $tag;
$tplVars['referrer']    = $_SERVER['HTTP_REFERER'];
$templateservice->loadTemplate('tag2tagadd.tpl', $tplVars);
?>
