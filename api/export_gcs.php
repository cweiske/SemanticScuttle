<?php
/*
 Export for Google Custom Search
 */

// Force HTTP authentication first!
//require_once('httpauth.inc.php');
require_once('../header.inc.php');

$bookmarkservice =& ServiceFactory::getServiceInstance('BookmarkService');
$userservice =& ServiceFactory::getServiceInstance('UserService');

/*
 // Restrict to admins?
 if(!$userservice->isAdmin($userservice->getCurrentUserId())) {
 die(T_('You are not allowed to do this action (admin access)'));
 }*/

// Check if queried format is xml
if (isset($_REQUEST['xml']) && (trim($_REQUEST['xml']) == 1))
$xml = true;
else
$xml = false;

// Check to see if a tag was specified.
if (isset($_REQUEST['tag']) && (trim($_REQUEST['tag']) != ''))
$tag = trim($_REQUEST['tag']);
else
$tag = NULL;

// Get the posts relevant to the passed-in variables.
$bookmarks =& $bookmarkservice->getBookmarks(0, NULL, NULL, $tag, NULL, getSortOrder());

$currentuser = $userservice->getCurrentUser();
$currentusername = $currentuser[$userservice->getFieldName('username')];

// Set up the plain file and output all the posts.
header('Content-Type: text/plain');
if(!$xml) {
	header('Content-Type: text/plain');
	foreach($bookmarks['bookmarks'] as $row) {
		if(checkUrl($row['bAddress'], false)) {
			echo $row['bAddress']."\n";
		}
	}
} else {
	header('Content-Type: application/xml');
	echo '<GoogleCustomizations>'."\n";
	echo '  <Annotations>'."\n";
	foreach($bookmarks['bookmarks'] as $row) {
		//if(substr($row['bAddress'], 0, 7) == "http://") {
		if(checkUrl($row['bAddress'], false)) {
			echo '    <Annotation about="'.filter($row['bAddress']).'">'."\n";
			echo '      <Label name="include"/>'."\n";
			echo '    </Annotation>'."\n";
		}
	}
	echo '  </Annotations>'."\n";
	echo '</GoogleCustomizations>'."\n";
}

?>
