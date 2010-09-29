<?php
// Implements the del.icio.us API request to add a new post.
// http://delicious.com/help/api#posts_add

// del.icio.us behavior:
// - tags can't have spaces
// - address and description are mandatory
// - description == title in semanticscuttle
// - extended == description in semanticscuttle

// Scuttle behavior:
// - Additional 'status' variable for privacy
// - No support for 'replace' variable

// Force HTTP authentication
$httpContentType = 'text/xml';
require_once 'httpauth.inc.php';

/* Service creation: only useful services are created */
$bookmarkservice =SemanticScuttle_Service_Factory::get('Bookmark');

// Get all the bookmark's passed-in information
if (isset($_REQUEST['url']) && (trim($_REQUEST['url']) != ''))
    $url = trim(urldecode($_REQUEST['url']));
else
    $url = NULL;

if (isset($_REQUEST['description']) && (trim($_REQUEST['description']) != ''))
    $description = trim($_REQUEST['description']);
else
    $description = NULL;

if (isset($_REQUEST['extended']) && (trim($_REQUEST['extended']) != ""))
    $extended = trim($_REQUEST['extended']);
else
    $extended = NULL;

if (isset($_REQUEST['tags']) && (trim($_REQUEST['tags']) != '') && (trim($_REQUEST['tags']) != ','))
    $tags = trim($_REQUEST['tags']);
else
    $tags = NULL;

if (isset($_REQUEST['dt']) && (trim($_REQUEST['dt']) != ''))
    $dt = trim($_REQUEST['dt']);
else
    $dt = NULL;

$status = 0;
if (isset($_REQUEST['status'])) {
    $status_str = trim($_REQUEST['status']);
    if (is_numeric($status_str)) {
        $status = intval($status_str);
        if($status < 0 || $status > 2) {
            $status = 0;
        }
    } else {
        switch ($status_str) {
            case 'private':
                $status = 2;
                break;
            case 'shared':
                $status = 1;
                break;
            default:
                $status = 0;
                break;
        }
    }
}

if (isset($_REQUEST['shared']) && (trim($_REQUEST['shared']) == 'no')) {
    $status = 2;
}

// Error out if there's no address or description
if (is_null($url) || is_null($description)) {
    $added = false;
} else {
// We're good with info; now insert it!
    if ($bookmarkservice->bookmarkExists($url, $userservice->getCurrentUserId()))
        $added = false;
    else
        $added = $bookmarkservice->addBookmark($url, $description, $extended, '', $status, $tags, null, $dt, true);
}

// Set up the XML file and output the result.
echo '<?xml version="1.0" standalone="yes" ?'.">\r\n";
echo '<result code="'. ($added ? 'done' : 'something went wrong') .'" />';
?>