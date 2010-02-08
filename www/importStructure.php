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
$tag2tagservice =SemanticScuttle_Service_Factory::get('Tag2Tag');

/* Managing current logged user */
$currentUser = $userservice->getCurrentObjectUser();


/* Managing all possible inputs */
// First input is $_FILES

$tplVars['msg'] = '';

if ($userservice->isLoggedOn() && sizeof($_FILES) > 0 && $_FILES['userfile']['size'] > 0) {
	$userinfo = $userservice->getCurrentObjectUser();


	// File handle
	$html = file_get_contents($_FILES['userfile']['tmp_name']);

	// Create link array
	preg_match_all('/(.*?)\n/', $html, $matches);

	//print_r($matches); die();

	$fatherTag = '';
	$countNewLinks = 0;
	foreach($matches[1] as $match) {
		if($match == '') {
			// do nothing because void line
		}elseif(substr($match, 0, 2) == '//') {
			// do nothing because commentary
		} elseif(substr($match, 0, 2) == '  ') {
			// add as child of previous tag
			if($fatherTag != '') {
				$tag2tagservice->addLinkedTags($fatherTag, $match, '>', $currentUser->getId());
				$countNewLinks++;
			} else {
				$tplVars['error'] = T_('Bad indentation'). ' '.$match;
			}
		} else{
			$fatherTag = $match;
		}
	}
	$tplVars['msg'] = T_('New links between tags: ').$countNewLinks;

}

$templatename = 'importStructure.tpl';
$tplVars['subtitle'] = T_('Import Structure');
$tplVars['formaction'] = createURL('importStructure');
$templateservice->loadTemplate($templatename, $tplVars);

?>
