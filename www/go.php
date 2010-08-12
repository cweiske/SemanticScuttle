<?php
/**
 * SemanticScuttle - your social bookmark manager.
 *
 * Short URL redirection service.
 * Just call http://example.org/go/shortname
 * to get redirected to it. Helpful to get static URLs for
 * moving targets.
 *
 * PHP version 5.
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */
$httpContentType = false;
require_once 'www-header.php';

if (!$GLOBALS['shorturl']) {
    header('HTTP/1.0 500 Internal Server Error');
    header('Content-Type: text/plain');
    echo 'Short URL service deactivated';
    exit();
}

if (!isset($_SERVER['PATH_INFO'])) {
    header('HTTP/1.0 400 Bad Request');
    header('Content-Type: text/plain');
    echo 'Short URL name missing';
    exit();
}

list($url, $short) = explode('/', $_SERVER['PATH_INFO']);

$bs = SemanticScuttle_Service_Factory::get('Bookmark');
$bookmark = $bs->getBookmarkByShortname($short);
if ($bookmark === false) {
    header('HTTP/1.0 404 Not found');
    header('Content-Type: text/plain');
    echo 'No bookmark found with short name of: ' . $short;
    exit();
}

header('HTTP/1.0 302 Found');
header('Location: ' . $bookmark['bAddress']);
?>