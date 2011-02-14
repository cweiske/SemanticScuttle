<?php
/**
 * API for deleting a bookmark.
 * The delicious API is implemented here.
 *
 * The delicious API behaves like that:
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
 * @link     http://www.delicious.com/help/api
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
    $msg = 'something went wrong';
} else if (!$bs->bookmarkExists($_REQUEST['url'], $uId)) {
    //the user does not have such a bookmark
    header('HTTP/1.0 404 Not Found');
    $msg = 'item not found';
} else {
    $bookmark = $bs->getBookmarkByAddress($_REQUEST['url'], false);
    $bId      = $bookmark['bId'];
    $deleted  = $bs->deleteBookmark($bId);
    $msg      = 'done';
    if (!$deleted) {
        //something really went wrong
        header('HTTP/1.0 500 Internal Server Error');
        $msg = 'something really went wrong';
    }
}

// Set up the XML file and output the result.
echo '<?xml version="1.0" standalone="yes" ?' . ">\r\n";
echo '<result code="' . $msg . '" />';
?>