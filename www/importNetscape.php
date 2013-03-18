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
$bookmarkservice =SemanticScuttle_Service_Factory::get('Bookmark');


/* Managing all possible inputs */
// First input is $_FILES
// Other inputs
isset($_POST['status']) ? define('POST_STATUS', $_POST['status']): define('POST_STATUS', $GLOBALS['defaults']['privacy']);

$countImportedBookmarks = 0;
$tplVars['msg'] = '';

if ($userservice->isLoggedOn() && sizeof($_FILES) > 0 && $_FILES['userfile']['size'] > 0) {
	$userinfo = $userservice->getCurrentObjectUser();

	if (is_numeric(POST_STATUS)) {
		$status = intval(POST_STATUS);
	} else {
		$status = $GLOBALS['defaults']['privacy'];
	}

	// File handle
	$html = file_get_contents($_FILES['userfile']['tmp_name']);

	// Create link array
	//preg_match_all('/<a\s+(.*?)\s*\/*>([^<]*)/si', $html, $matches);
	preg_match_all('/<a\s+(.*?)>([^<]*?)<\/a>.*?(<dd>([^<]*)|<dt>)/si', $html, $matches);

	//var_dump($matches);die();


	$links = $matches[1];
	$titles = $matches[2];
	$descriptions = $matches[4];

	$size = count($links);
	for ($i = 0; $i < $size; $i++) {

		//    	echo "<hr/>";
		//    	echo $links[$i]."<br/>";
			
		preg_match_all('/(\w*\s*=\s*"[^"]*")/', $links[$i], $attributes);
		//$attributes = $attributes[0];  // we keep just one row

		$bDatetime = ""; //bDateTime optional
		$bCategories = ""; //bCategories optional
		$bPrivateNote = ""; //bPrivateNote optional
		$bPrivate = $status; //bPrivate set default

		foreach ($attributes[0] as $attribute) {
			$att = preg_split('/\s*=\s*/s', $attribute, 2);
			$attrTitle = $att[0];

			$attrVal = str_replace(
				'&quot;', '"',
				preg_replace('/([\'"]?)(.*)\1/', '$2', $att[1])
			);

			switch ($attrTitle) {
				case "HREF":
					$bAddress = $attrVal;
					break;
				case "ADD_DATE":
					$bDatetime = gmdate('Y-m-d H:i:s', $attrVal);
					break;
				case "TAGS":
					$bCategories = $attrVal;
					break;
				case "NOTE":
					$bPrivateNote = $attrVal;
					break;
				case "PRIVATE":
					if ($attrVal) {
						$bPrivate = 2;//private
					}
			}
		}
		$bTitle = trim($titles[$i]);
		$bDescription = trim($descriptions[$i]);

		if ($bookmarkservice->bookmarkExists($bAddress, $userservice->getCurrentUserId())) {
			$tplVars['error'] = T_('You have already submitted some of these bookmarks.');
		} else {
			// If bookmark is local (like javascript: or place: in Firefox3), do nothing
			if(substr($bAddress, 0, 7) == "http://" || substr($bAddress, 0, 8) == "https://") {

				// If bookmark claims to be from the future, set it to be now instead
				if (strtotime($bDatetime) > time()) {
					$bDatetime = gmdate('Y-m-d H:i:s');
				}

				if ($bookmarkservice->addBookmark($bAddress, $bTitle, $bDescription, $bPrivateNote, $bPrivate, $bCategories, null, $bDatetime, false, true)) {
					$countImportedBookmarks++;
				} else {
					$tplVars['error'] = T_('There was an error saving your bookmark. Please try again or contact the administrator.');
				}
			}
		}
	}
	//header('Location: '. createURL('bookmarks', $userinfo->getUsername()));
	$templatename = 'importNetscape.tpl';
	$tplVars['msg'].= T_('Bookmarks found: ').$size.' ';
	$tplVars['msg'].= T_('Bookmarks imported: ').' '.$countImportedBookmarks;
	$tplVars['subtitle'] = T_('Import Bookmarks from Browser File');
	$tplVars['formaction'] = createURL('importNetscape');
	$templateservice->loadTemplate($templatename, $tplVars);
} else {
	$templatename = 'importNetscape.tpl';
	$tplVars['subtitle'] = T_('Import Bookmarks from Browser File');
	$tplVars['formaction'] = createURL('importNetscape');
	$templateservice->loadTemplate($templatename, $tplVars);
}
?>
