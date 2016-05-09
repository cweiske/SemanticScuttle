#!/usr/bin/env php
<?php
/**
 * CLI tool to add bookmarks to SemanticScuttle.
 * Intended as end point for a chat bot, e.g. "errbot-exec".
 *
 * Parameters:
 * 1. Message with bookmark url and tags
 * 2. E-Mail address of user
 *
 * You may map chat users to semanticscuttle email addresses
 * with the $botMailMap config variable
 *
 * @author Christian Weiske <cweiske@cweiske.de>
 * @link   https://github.com/cweiske/errbot-exec
 */
require_once dirname(__FILE__) . '/../src/SemanticScuttle/header-standalone.php';

if ($argc < 3) {
    err('No message and user', 1);
}
$msg   = $argv[1];
$email = $argv[2];

if (preg_match('#(.+@.+)/.*#', $email, $matches)) {
    //xmpp user name with resource: user@example.org/client
    $email = $matches[1];
}
if (isset($botMailMap[$email])) {
    $email = $botMailMap[$email];
}

function err($msg, $code)
{
    echo $msg . "\n";
    exit($code);
}

function getUserId($email)
{
    $users = SemanticScuttle_Service_Factory::get('User');
    if (!$users->isValidEmail($email)) {
        err('Invalid e-mail address: ' . $email, 2);
    }
    $db = SemanticScuttle_Service_Factory::getDb();
    $res = $db->sql_query(
        'SELECT uId FROM '. $users->getTableName()
        . ' WHERE email = "' . $db->sql_escape($email) . '"'
    );
    $row = $db->sql_fetchrow($res);
    if (!is_array($row)) {
        err('User not found: ' . $email, 3);
    }
    return intval($row['uId']);
}

function splitMsg($msg)
{
    $bmUrl = $msg;
    $rest = '';
    if (strpos($msg, ' ') !== false) {
        list($bmUrl, $rest) = explode(' ', $msg, 2);
    }
    $parts = parse_url($bmUrl);
    if (!isset($parts['scheme'])) {
        err('Scheme missing in URL', 2);
    }
    if (!SemanticScuttle_Model_Bookmark::isValidUrl($bmUrl)) {
        err('Invalid bookmark URL', 2);
    }

    $bmTags = array();
    $bmDesc = '';
    $rest = trim($rest);
    if (strlen($rest) && $rest{0} == '#') {
        //tags begin with '#'
        preg_match_all('/#([a-zA-Z0-9]+)/', $rest, $matches);
        $bmTags = $matches[1];
        foreach ($matches[0] as $tag) {
            if (substr($rest, 0, strlen($tag)) == $tag) {
                $rest = trim(substr($rest, strlen($tag)));
            }
        }
        $bmDesc = $rest;
    } else {
        //use rest as tags
        $bmTags = explode(' ', $rest);
        $bmTags = array_map('trim', $bmTags);
    }

    return array($bmUrl, $bmTags, $bmDesc);
}

$userId = getUserId($email);
list($bmUrl, $bmTags, $bmDesc) = splitMsg($msg);

$bookmarks = SemanticScuttle_Service_Factory::get('Bookmark');
if ($bookmarks->bookmarkExists($bmUrl)) {
    echo "URL already bookmarked.\n";
    exit(0);
}

$urlhelper = new SemanticScuttle_UrlHelper();
$bmTitle   = $urlhelper->getTitle($bmUrl);

$id = $bookmarks->addBookmark(
    $bmUrl,
    $bmTitle,
    $bmDesc,
    null,
    SemanticScuttle_Model_Bookmark::SPUBLIC,
    $bmTags,
    null,
    null,
    true,
    false,
    $userId
);
if ($id === false) {
    err('Error adding bookmark', 10);
} else {
    echo "Bookmark created.\n";
    exit(0);
}
?>
