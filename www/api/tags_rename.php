<?php
// Implements the del.icio.us API request to rename a user's tag.

// del.icio.us behavior:
// - oddly, returns an entirely different result (<result></result>) than the other API calls.

// Force HTTP authentication first!
$httpContentType = 'text/xml';
require_once 'httpauth.inc.php';

/* Service creation: only useful services are created */
$b2tservice =SemanticScuttle_Service_Factory::get('Bookmark2Tag');

// Get the tag info.
if (isset($_REQUEST['old']) && (trim($_REQUEST['old']) != ''))
    $old = trim($_REQUEST['old']);
else
    $old = NULL;

if (isset($_REQUEST['new']) && (trim($_REQUEST['new']) != ''))
    $new = trim($_REQUEST['new']);
else
    $new = NULL;

if (is_null($old) || is_null($new)) {
    $renamed = false;
} else {
    // Rename the tag.
    $result = $b2tservice->renameTag($userservice->getCurrentUserId(), $old, $new, true);
    $renamed = $result;
}

// Set up the XML file and output the result.
echo '<?xml version="1.0" standalone="yes" ?'.">\r\n";
echo '<result>'. ($renamed ? 'done' : 'something went wrong') .'</result>';
?>
