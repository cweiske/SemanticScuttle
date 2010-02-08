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
$b2tservice = SemanticScuttle_Service_Factory :: get('Bookmark2Tag');


/* Managing all possible inputs */
isset($_POST['confirm']) ? define('POST_CONFIRM', $_POST['confirm']): define('POST_CONFIRM', '');
isset($_POST['cancel']) ? define('POST_CANCEL', $_POST['cancel']): define('POST_CANCEL', '');
isset($_POST['referrer']) ? define('POST_REFERRER', $_POST['referrer']): define('POST_REFERRER', '');

/* Managing current logged user */
$currentUser = $userservice->getCurrentObjectUser();

/* Managing path info */
list ($url, $tag) = explode('/', $_SERVER['PATH_INFO']);



if (POST_CONFIRM) {
    if ($b2tservice->deleteTag($currentUser->getId(), $tag)) {
        $tplVars['msg'] = T_('Tag deleted');        
        header('Location: '. createURL('bookmarks', $currentUser->getUsername()));
    } else {
        $tplVars['error'] = T_('Failed to delete the tag');
        $templateservice->loadTemplate('error.500.tpl', $tplVars);
        exit();
    }
} elseif (POST_CANCEL) {
    header('Location: '. POST_REFERRER);
}

$tplVars['subtitle']    = T_('Delete Tag') .': '. $tag;
$tplVars['formaction']  = $_SERVER['SCRIPT_NAME'] .'/'. $tag;
$tplVars['referrer']    = $_SERVER['HTTP_REFERER'];
$templateservice->loadTemplate('tagdelete.tpl', $tplVars);
?>
