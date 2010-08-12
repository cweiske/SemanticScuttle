<?php
/**
 * Implements the del.icio.us API request for a user's posts,
 * optionally filtered by tag and/or date.
 * Note that when using a date to select the posts returned, del.icio.us
 * uses GMT dates -- so we do too.
 *
 * del.icio.us behavior:
 * - includes an empty tag attribute on the root element when it hasn't been specified
 *
 * Scuttle behavior:
 * - Uses today, instead of the last bookmarked date, if no date is specified
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

// Check to see if a date was specified; the format should be YYYY-MM-DD
if (isset($_REQUEST['dt']) && (trim($_REQUEST['dt']) != '')) {
    $dtstart = trim($_REQUEST['dt']);
} else {
    $dtstart = date('Y-m-d H:i:s');
}
$dtend = date('Y-m-d H:i:s', strtotime($dtstart .'+1 day'));

// Get the posts relevant to the passed-in variables.
$bookmarks = $bookmarkservice->getBookmarks(
    0, null, $userservice->getCurrentUserId(), $tag,
    null, null, null, $dtstart, $dtend
);


// Set up the XML file and output all the tags.
echo '<?xml version="1.0" standalone="yes" ?'.">\r\n";
echo '<posts'. (is_null($dtstart) ? '' : ' dt="'. $dtstart .'"') .' tag="'. (is_null($tag) ? '' : filter($tag, 'xml')) .'" user="'. filter($currentUser->getUsername(), 'xml') ."\">\r\n";

foreach ($bookmarks['bookmarks'] as $row) {
    if (is_null($row['bDescription']) || (trim($row['bDescription']) == '')) {
        $description = '';
    } else {
        $description = 'extended="'. filter($row['bDescription'], 'xml') .'" ';
    }

    $taglist = '';
    if (count($row['tags']) > 0) {
        foreach ($row['tags'] as $tag) {
            $taglist .= convertTag($tag) . ' ';
        }
        $taglist = substr($taglist, 0, -1);
    } else {
        $taglist = 'system:unfiled';
    }

    echo "\t<post href=\"". filter($row['bAddress'], 'xml') .'" description="'. filter($row['bTitle'], 'xml') .'" '. $description .'hash="'. $row['bHash'] .'" others="'. $bookmarkservice->countOthers($row['bAddress']) .'" tag="'. filter($taglist, 'xml') .'" time="'. gmdate('Y-m-d\TH:i:s\Z', strtotime($row['bDatetime'])) ."\" />\r\n";
}

echo '</posts>';
?>