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
foreach($bookmarks['bookmarks'] as $row) {
    echo $row['bAddress']."\n";
}


?>
