<?php
/**
 * API for deleting a bookmark.
 * The delicious API is implemented here.
 *
 * The delicious API behaves like that:
 * - returns "done" even if the bookmark doesn't exist
 *   - we do it correctly
 * - does NOT allow the hash for the url parameter
 * - doesn't set the Content-Type to text/xml
 *   - we do it correctly, too
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

$bs  = SemanticScuttle_Service_Factory::get('Bookmark');
$uId = $userservice->getCurrentUserId();


// Error out if there's no address
if (!isset($_REQUEST['url'])
    || $_REQUEST['url'] == ''
) {
    $deleted = false;
} else if (!$bs->bookmarkExists($_REQUEST['url'], $uId)) {
    //the user does not have such a bookmark
    // Note that del.icio.us only errors out if no URL was passed in;
    // there's no error on attempting to delete a bookmark you don't have.
    // this sucks, and I don't care about being different but correct here.
    header('HTTP/1.0 404 Not Found');
    $deleted = false;

} else {
    $bookmark = $bs->getBookmarkByAddress($_REQUEST['url'], false);
    $bId      = $bookmark['bId'];
    $deleted  = $bs->deleteBookmark($bId);
    if (!$deleted) {
        //something really went wrong
        header('HTTP/1.0 500 Internal Server Error');
    }
}

// Set up the XML file and output the result.
echo '<?xml version="1.0" standalone="yes" ?' . ">\r\n";
echo '<result code="' . ($deleted ? 'done' : 'something went wrong') . '" />';
?>