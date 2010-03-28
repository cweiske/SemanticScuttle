<?php
/**
 * Export own bookmarks in CSV format in order to allow the import
 * into a spreadsheet tool like Excel
 *
 * PHP version 5.
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Benjamin Huynh-Kim-Bang <mensonge@users.sourceforge.net>
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @author   Eric Dane <ericdane@users.sourceforge.net>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */

// Force HTTP authentication first!
$httpContentType = 'application/csv-tab-delimited-table';
require_once 'httpauth.inc.php';
header("Content-disposition: filename=exportBookmarks.csv");

/* Service creation: only useful services are created */
$bookmarkservice =SemanticScuttle_Service_Factory::get('Bookmark');

// Check to see if a tag was specified.
if (isset($_REQUEST['tag']) && (trim($_REQUEST['tag']) != '')) {
    //$_GET vars have + replaced to " " automatically
    $tag = str_replace(' ', '+', trim($_REQUEST['tag']));
} else {
    $tag = null;
}

// Get the posts relevant to the passed-in variables.
$bookmarks = $bookmarkservice->getBookmarks(
    0, null, $userservice->getCurrentUserId(),
    $tag, null, getSortOrder()
);

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
