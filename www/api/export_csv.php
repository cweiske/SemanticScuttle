<?php
// Export in CSV format in order to allow the import into a spreadsheet tool like Excel

// Force HTTP authentication first!
$httpContentType = 'application/csv-tab-delimited-table';
require_once 'httpauth.inc.php';
header("Content-disposition: filename=exportBookmarks.csv");

/* Service creation: only useful services are created */
$bookmarkservice =SemanticScuttle_Service_Factory::get('Bookmark');

// Check to see if a tag was specified.
if (isset($_REQUEST['tag']) && (trim($_REQUEST['tag']) != ''))
    $tag = trim($_REQUEST['tag']);
else
    $tag = NULL;

// Get the posts relevant to the passed-in variables.
$bookmarks =& $bookmarkservice->getBookmarks(0, NULL, $userservice->getCurrentUserId(), $tag, NULL, getSortOrder());

//columns titles
echo 'url;title;tags;description';
echo "\n";

foreach($bookmarks['bookmarks'] as $row) {
    if (is_null($row['bDescription']) || (trim($row['bDescription']) == ''))
        $description = '';
    else
        $description = filter(str_replace(array("\r\n", "\n", "\r"),"", $row['bDescription']), 'xml');

    $taglist = '';
    if (count($row['tags']) > 0) {
        foreach($row['tags'] as $tag)
            $taglist .= convertTag($tag) .',';
        $taglist = substr($taglist, 0, -1);
    } else {
        $taglist = 'system:unfiled';
    }

    echo '"'.filter($row['bAddress'], 'xml') .'";"'. filter($row['bTitle'], 'xml') .'";"'. filter($taglist, 'xml') .'";"'. $description .'"';
    echo "\n";
}


?>
