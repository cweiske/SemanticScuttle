<?php
/**
 * We re-use vote.php but set the ajax flag
 */
$httpContentType = 'text/xml';
$GLOBALS['ajaxRequest'] = true;
require 'vote.php';

$bs = SemanticScuttle_Service_Factory::get('Bookmark');
$ts = SemanticScuttle_Service_Factory::get('Template');
$bmrow = $bs->getBookmark($bookmark);

switch ($GLOBALS['votingMode']) {
case 2:
    $template = 'bookmarks-vote-horizontal.inc.tpl.php';
    break;
default:
    $template = 'bookmarks-vote.inc.tpl.php';
}

echo '<voteresult><bookmark>' . $bookmark . '</bookmark>'
    . '<html xmlns="http://www.w3.org/1999/xhtml">';
$ts->loadTemplate(
    $template, array('row' => $bmrow)
);

echo '</html></voteresult>';
?>