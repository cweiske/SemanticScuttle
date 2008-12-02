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

/* Return a json file with list of linked tags */

require_once('../header.inc.php');

/* Service creation: only useful services are created */
$b2tservice =& ServiceFactory::getServiceInstance('Bookmark2TagService');
$bookmarkservice =& ServiceFactory::getServiceInstance('TagService');
$userservice =& ServiceFactory::getServiceInstance('UserService');
$tagstatservice =& ServiceFactory::getServiceInstance('TagStatService');

/* Managing all possible inputs */
isset($_GET['tag']) ? define('GET_TAG', $_GET['tag']): define('GET_TAG', '');
isset($_GET['uId']) ? define('GET_UID', $_GET['uId']): define('GET_UID', '');


function displayTag($tag, $uId) {
	$tag2tagservice =& ServiceFactory::getServiceInstance('Tag2TagService');
	$output =  '{ id:'.rand().', name:\''.$tag.'\'';

	$linkedTags = $tag2tagservice->getLinkedTags($tag, '>', $uId);
	if(count($linkedTags) > 0) {
		$output.= ', children: [';
		foreach($linkedTags as $linkedTag) {
			$output.= displayTag($linkedTag, $uId);
		}
		$output.= "]";
	}

	$output.= '},';
	return $output;
}

?>

{ label: 'name', identifier: 'id', items: [
<?php
echo displayTag(GET_TAG, GET_UID);
?>
] }
