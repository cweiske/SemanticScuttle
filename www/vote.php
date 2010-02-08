<?php
/**
 * We do expect three parameters:
 * - type (for/against)
 * - bookmark id
 * - session needs to contain the URL last visited
 *
 * vote/for/123
 */
require_once 'www-header.php';

if (!$GLOBALS['enableVoting']) {
    header('HTTP/1.0 501 Not implemented');
    echo 'voting is disabled';
    exit(1);
}


$us = SemanticScuttle_Service_Factory::get('User');
$vs = SemanticScuttle_Service_Factory::get('Vote');

if (!$us->isLoggedOn()) {
    header('HTTP/1.0 400 Bad Request');
    echo 'You need to be logged on to vote.';
    exit(1);
}
$user = $us->getCurrentUser();
$user = $user['uId'];

if (!isset($_SERVER['PATH_INFO'])) {
    //we got a problem
    header('HTTP/1.0 500 Internal Server Error');
    echo 'PATH_INFO not found';
    exit(2);
}

//we should really use net_url_mapper here
list($url, $type, $bookmark) = explode('/', $_SERVER['PATH_INFO']);

if ($type != 'for' && $type != 'against') {
    header('HTTP/1.0 400 Bad Request');
    echo 'type has to be "for" or "against"';
    exit(3);
}
if (!is_numeric($bookmark)) {
    header('HTTP/1.0 400 Bad Request');
    echo 'Bookmark must be numeric';
    exit(4);
}
$bookmark = (int)$bookmark;

if (!isset($GLOBALS['lastUrl']) || $GLOBALS['lastUrl'] == '') {
    header('HTTP/1.0 412 Precondition failed');
    echo 'Missing last URL in session';
    exit(5);
}
$from = $GLOBALS['lastUrl'];


$vs->vote($bookmark, $user, $type == 'for' ? 1 : -1);

if (isset($GLOBALS['ajaxRequest']) && $GLOBALS['ajaxRequest']) {
    //we are in ajax mode and return the badge in ajaxVote.php
    return;
}

header('Location: ' . $from);
?>