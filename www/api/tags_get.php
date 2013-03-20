<?php
// Implements the del.icio.us API request for all a user's tags.

// del.icio.us behavior:
// - tags can't have spaces

// Force HTTP authentication first!
$httpContentType = 'text/xml';
require_once 'httpauth.inc.php';

/* Service creation: only useful services are created */
$b2tservice =SemanticScuttle_Service_Factory::get('Bookmark2Tag');


// Get the tags relevant to the passed-in variables.
$tags = $b2tservice->getTags($userservice->getCurrentUserId());

// Set up the XML file and output all the tags.
echo '<?xml version="1.0" standalone="yes" ?'.">\r\n";
echo "<tags>\r\n";
foreach($tags as $row) {
    echo "\t<tag count=\"". $row['bCount'] .'" tag="'. filter(convertTag($row['tag'], 'out'), 'xml') ."\" />\r\n";
}
echo "</tags>";
?>
