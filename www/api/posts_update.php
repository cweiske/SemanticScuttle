<?php
/**
 * API for retrieving a user's last update time.
 * That is the time the user changed a bookmark lastly.
 * The delicious API is implemented here.
 *
 * Delicious also returns "the number of new items in
 *  the user's inbox since it was last visited." - we do
 * that too, so we are as close at the API as possible,
 * not breaking delicious clients.
 *
 * SemanticScuttle supports an extra parameter:
 * - ?datemode=modified
 *   - sorts by modified date and returns modification time
 *     instead of creation time
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
 * @link     http://www.delicious.com/help/api
 */

// Force HTTP authentication first!
$httpContentType = 'text/xml';
require_once 'httpauth.inc.php';

// parameter "datemode=modified" will get last modified date
// instead of last created date
$orderby   = null;
$timeField = 'bDatetime';
if (isset($_GET['datemode']) && $_GET['datemode'] == 'modified') {
    $orderby = 'modified_desc';
    $timeField = 'bModified';
}

$bs = SemanticScuttle_Service_Factory::get('Bookmark');

$bookmarks = $bs->getBookmarks(0, 1, $userservice->getCurrentUserId(), null, null, $orderby);

// Set up the XML file and output all the tags.
echo '<?xml version="1.0" standalone="yes" ?' . ">\r\n";
//foreach is used in case there are no bookmarks
foreach ($bookmarks['bookmarks'] as $row) {
    echo '<update time="'
        . gmdate('Y-m-d\TH:i:s\Z', strtotime($row[$timeField]))
        . '"'
        . ' inboxnew="0"'
        . ' />';
}
?>
