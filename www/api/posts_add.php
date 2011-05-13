<?php
/**
 * API for adding a new bookmark.
 *
 * The following POST and GET parameters are accepted:
 * @param string  $url         URL of the bookmark (required)
 * @param string  $description Bookmark title (required)
 * @param string  $extended    Extended bookmark description (optional)
 * @param string  $tags        Space-separated list of tags (optional)
 * @param string  $dt          Date and time of bookmark creation (optional)
 *                             Must be of format YYYY-MM-DDTHH:II:SSZ
 * @param integer $status      Visibility status (optional):
 *                             - 2 or 'private': Bookmark is totally private
 *                             - 1 or 'shared': People on the user's watchlist
 *                                              can see it
 *                             - 0 or 'public': Everyone can see the bookmark
 * @param string  $shared      "no" or "yes": Switches between private and
 *                             public (optional)
 * @param string  $replace     "yes" or "no" - replaces a bookmark with the
 *                             same URL (optional)
 *
 * Notes:
 * - tags cannot have spaces
 * - URL and description (title) are mandatory
 * - delicious "description" is the "title" in SemanticScuttle
 * - delicious "extended" is the "description" in SemanticScuttle
 * - "status" is a SemanticScuttle addition to this API method
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

// Force HTTP authentication
$httpContentType = 'text/xml';
require_once 'httpauth.inc.php';

$bs = SemanticScuttle_Service_Factory::get('Bookmark');

// Get all the bookmark's passed-in information
if (isset($_REQUEST['url']) && (trim($_REQUEST['url']) != '')) {
    $url = trim(urldecode($_REQUEST['url']));
} else {
    $url = null;
}

if (isset($_REQUEST['description']) && (trim($_REQUEST['description']) != '')) {
    $description = trim($_REQUEST['description']);
} else {
    $description = null;
}

if (isset($_REQUEST['extended']) && (trim($_REQUEST['extended']) != '')) {
    $extended = trim($_REQUEST['extended']);
} else {
    $extended = null;
}

if (isset($_REQUEST['tags']) && (trim($_REQUEST['tags']) != '')
    && (trim($_REQUEST['tags']) != ',')
) {
    $tags = trim($_REQUEST['tags']);
} else {
    $tags = null;
}

if (isset($_REQUEST['dt']) && (trim($_REQUEST['dt']) != '')) {
    $dt = trim($_REQUEST['dt']);
} else {
    $dt = null;
}

$replace = isset($_REQUEST['replace']) && ($_REQUEST['replace'] == 'yes');

$status = $GLOBALS['defaults']['privacy'];
if (isset($_REQUEST['status'])) {
    $status_str = trim($_REQUEST['status']);
    if (is_numeric($status_str)) {
        $status = intval($status_str);
        if ($status < 0 || $status > 2) {
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
if (is_null($url)) {
    header('HTTP/1.0 400 Bad Request');
    $msg = 'URL missing';
} else if (is_null($description)) {
    header('HTTP/1.0 400 Bad Request');
    $msg = 'Description missing';
} else {
    // We're good with info; now insert it!
    $exists = $bs->bookmarkExists($url, $userservice->getCurrentUserId());
    if ($exists) {
        if (!$replace) {
            header('HTTP/1.0 409 Conflict');
            $msg = 'bookmark does already exist';
        } else {
            //delete it before we re-add it
            $bookmark = $bs->getBookmarkByAddress($url, false);
            $bId      = $bookmark['bId'];
            $bs->deleteBookmark($bId);

            $exists = false;
        }
    }

    if (!$exists) {
        $added = $bs->addBookmark(
            $url, $description, $extended, '', $status, $tags, null, $dt, true
        );
        $msg = 'done';
    }
}

// Set up the XML file and output the result.
echo '<?xml version="1.0" standalone="yes" ?' . ">\r\n";
echo '<result code="' . $msg .'" />';
?>