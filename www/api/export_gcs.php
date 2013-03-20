<?php
/*
 Export for Google Custom Search
 */

// Force HTTP authentication first!
//require_once('httpauth.inc.php');
$httpContentType = false;
require_once '../www-header.php';

if($GLOBALS['enableGoogleCustomSearch'] == false) {
    echo "Google Custom Search disabled. You can enable it into the config.php file.";
    die;
}

/* Service creation: only useful services are created */
$bookmarkservice =SemanticScuttle_Service_Factory::get('Bookmark');


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
$bookmarks = $bookmarkservice->getBookmarks(0, NULL, NULL, $tag, NULL, getSortOrder());


// Set up the plain file and output all the posts.
header('Content-Type: text/plain; charset=utf-8');
if(!$xml) {
	header('Content-Type: text/plain');
	foreach($bookmarks['bookmarks'] as $row) {
		if(checkUrl($row['bAddress'], false)) {
			echo $row['bAddress']."\n";
		}
	}
} else {
	header('Content-Type: text/xml');
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
