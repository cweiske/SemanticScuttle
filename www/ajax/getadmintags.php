<?php
/**
 * Return a json file with list of public tags used by admins and sorted
 * by popularity.
 *
 * The following GET parameters are accepted:
 * @param string  $beginsWith The tag name shall start with that string.
 *                            No default.
 * @param integer $limit      Number of tags to return. Defaults to 1000
 *
 * Part of SemanticScuttle - your social bookmark manager.
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

$httpContentType = 'application/json';
require_once '../www-header.php';

$limit         = 30;
$beginsWith    = null;
$currentUserId = $userservice->getCurrentUserId();

if (isset($_GET['limit']) && is_numeric($_GET['limit'])) {
    $limit = (int)$_GET['limit'];
}
if (isset($_GET['beginsWith']) && strlen(trim($_GET['beginsWith']))) {
    $beginsWith = trim($_GET['beginsWith']);
}

$listTags = SemanticScuttle_Service_Factory::get('Bookmark2Tag')->getAdminTags(
    $limit, $currentUserId, null, $beginsWith
);
$tags = array();
foreach ($listTags as $t) {
    $tags[] = $t['tag'];
}

echo json_encode($tags);
?>