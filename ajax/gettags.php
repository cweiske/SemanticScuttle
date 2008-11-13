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

/* Return a json file with list of tags according to current user and sort by popularity*/

require_once('../header.inc.php');

$b2tservice =& ServiceFactory::getServiceInstance('Bookmark2TagService');
$bookmarkservice =& ServiceFactory::getServiceInstance('TagService');
$userservice =& ServiceFactory::getServiceInstance('UserService');

if ($userservice->isLoggedOn()) {
    $loggedon = true;
    $currentUser = $userservice->getCurrentUser();
    $currentUserID = $userservice->getCurrentUserId();
    $currentUsername = $currentUser[$userservice->getFieldName('username')];
}

?>

{identifier:"tag",
items: [
<?php
	$listTags = $b2tservice->getPopularTags($currentUserID, 1000, $currentUserID);
	foreach($listTags as $t) {
		echo "{tag: \"".$t['tag']."\"},";
	}
?>
]}




