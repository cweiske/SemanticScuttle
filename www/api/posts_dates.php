<?php
/**
 * Implements the del.icio.us API request for a user's post counts by date
 * (and optionally by tag).
 *
 * SemanticScuttle - your social bookmark manager.
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
$httpContentType = 'text/xml';
require_once 'httpauth.inc.php';

/* Service creation: only useful services are created */
$bookmarkservice = SemanticScuttle_Service_Factory::get('Bookmark');


// Check to see if a tag was specified.
if (isset($_REQUEST['tag']) && (trim($_REQUEST['tag']) != '')) {
    $tag = trim($_REQUEST['tag']);
} else {
    $tag = null;
}

// Get the posts relevant to the passed-in variables.
$bookmarks = $bookmarkservice->getBookmarks(
    0, null, $userservice->getCurrentUserId(), $tag
);

//	Set up the XML file and output all the tags.
echo '<?xml version="1.0" standalone="yes" ?'.">\r\n";
echo '<dates tag="'. (is_null($tag) ? '' : filter($tag, 'xml')) .'" user="'. filter($currentUser->getUsername(), 'xml') ."\">\r\n";

$lastdate = null;
$count    = 0;
foreach ($bookmarks['bookmarks'] as $row) {
    $thisdate = gmdate('Y-m-d', strtotime($row['bDatetime']));
    if ($thisdate != $lastdate && $lastdate != null) {
        echo "\t<date count=\"". $count .'" date="'. $lastdate ."\" />\r\n";
        $count = 1;
    } else {
        ++$count;
    }
    $lastdate = $thisdate;
}
if ($lastdate !== null) {
    echo "\t<date count=\"". $count .'" date="'. $lastdate ."\" />\r\n";
}

echo "</dates>";
?>