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

require_once 'www-header.php';

/* Service creation: only useful services are created */
$bookmarkservice = SemanticScuttle_Service_Factory :: get('Bookmark');

/* Managing all possible inputs */
isset($_POST['submitted']) ? define('POST_SUBMITTED', $_POST['submitted']): define('POST_SUBMITTED', '');
isset($_POST['delete']) ? define('POST_DELETE', $_POST['delete']): define('POST_DELETE', '');

isset($_POST['title']) ? define('POST_TITLE', $_POST['title']): define('POST_TITLE', '');
isset($_POST['address']) ? define('POST_ADDRESS', $_POST['address']): define('POST_ADDRESS', '');
isset($_POST['description']) ? define('POST_DESCRIPTION', $_POST['description']): define('POST_DESCRIPTION', '');
isset($_POST['privateNote']) ? define('POST_PRIVATENOTE', $_POST['privateNote']): define('POST_PRIVATENOTE', '');
isset($_POST['status']) ? define('POST_STATUS', $_POST['status']): define('POST_STATUS', $GLOBALS['defaults']['privacy']);
isset($_POST['tags']) ? define('POST_TAGS', $_POST['tags']): define('POST_TAGS', '');

isset($_GET['popup']) ? define('GET_POPUP', $_GET['popup']): define('GET_POPUP', '');
isset($_POST['popup']) ? define('POST_POPUP', $_POST['popup']): define('POST_POPUP', '');
isset($_POST['referrer']) ? define('POST_REFERRER', $_POST['referrer']): define('POST_REFERRER', '');

// Header variables
$tplVars['pagetitle'] = T_('Edit Bookmark');
$tplVars['subtitle'] = T_('Edit Bookmark');
$tplVars['loadjs'] = true;

list ($url, $bookmark) = explode('/', $_SERVER['PATH_INFO']);	

if (!($row = $bookmarkservice->getBookmark(intval($bookmark), true))) {
    $tplVars['error'] = sprintf(T_('Bookmark with id %s not was not found'), $bookmark);
    $templateservice->loadTemplate('error.404.tpl', $tplVars);
    exit();
} else {

    if (!$bookmarkservice->editAllowed($row)) {
        $tplVars['error'] = T_('You are not allowed to edit this bookmark');
        $templateservice->loadTemplate('error.500.tpl', $tplVars);
        exit();
    } else if (POST_SUBMITTED != '') {
    
    	
    
        if (!POST_TITLE || !POST_ADDRESS) {
            $tplVars['error'] = T_('Your bookmark must have a title and an address');
        } else {
            // Update bookmark
            $bId = intval($bookmark);
            $address = trim(POST_ADDRESS);
            $title = trim(POST_TITLE);
            $description = trim(POST_DESCRIPTION);
            $privateNote = trim(POST_PRIVATENOTE);
            $status = intval(POST_STATUS);
            $tags = trim(POST_TAGS);            
            
            if (!$bookmarkservice->updateBookmark($bId, $address, $title, $description, $privateNote, $status, $tags)) {
                $tplvars['error'] = T_('Error while saving your bookmark');
            } else {
                if (POST_POPUP != '') {
                    //$tplVars['msg'] = (POST_POPUP != '') ? '<script type="text/javascript">window.close();</script>' : T_('Bookmark saved');
                    $tplVars['msg'] = '<script type="text/javascript">window.close();</script>';
                } elseif (POST_REFERRER != '') {
                	$tplVars['msg'] = T_('Bookmark saved');
                    header('Location: '. POST_REFERRER);
                } else {
                	$tplVars['msg'] = T_('Bookmark saved');
                    header('Location: '. createURL('bookmarks', $currentUser->getUsername()));
                }
            }
        }
    } else {
        if (POST_DELETE != '') {
            // Delete bookmark
            if ($bookmarkservice->deleteBookmark($bookmark)) {
            	if (POST_POPUP != '') {            		
            		$tplVars['msg'] = '<script type="text/javascript">window.close();</script>';
            	} elseif (POST_REFERRER != '') {
                    header('Location: '. POST_REFERRER);
                } else {
                    header('Location: '. createURL('bookmarks', $currentUser->getUsername()));
                }
            } else {
                $tplVars['error'] = T_('Failed to delete bookmark');
                $templateservice->loadTemplate('error.500.tpl', $tplVars);
                exit();
            }
        }
    }

    $tplVars['popup'] = (GET_POPUP) ? GET_POPUP : null;
    $tplVars['row'] = $row;
    $tplVars['formaction']  = createURL('edit', $bookmark);
    $tplVars['btnsubmit'] = T_('Save Changes');
    $tplVars['showdelete'] = true;
    $tplVars['referrer'] = '';
    if (isset($_SERVER['HTTP_REFERER'])) {
        $tplVars['referrer'] = $_SERVER['HTTP_REFERER'];
    }
    $templateservice->loadTemplate('editbookmark.tpl', $tplVars);
}
?>
