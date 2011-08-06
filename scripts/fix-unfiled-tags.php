<?php
/**
 * SemanticScuttle from approximately 0.94 up to 0.98.2, system:unfiled
 * tags were not created when adding new bookmarks with the web interface.
 *
 * This script adds system:unfiled tags for all bookmarks that have no
 * tags.
 */
require_once dirname(__FILE__) . '/../src/SemanticScuttle/header-standalone.php';

//needed to load the database object 
$bt = SemanticScuttle_Service_Factory::get('Bookmark2Tag');
$db = SemanticScuttle_Service_Factory::getDb();

$query = <<<SQL
SELECT b.bId
FROM sc_bookmarks AS b
 LEFT JOIN sc_bookmarks2tags AS bt ON b.bId = bt.bId
WHERE bt.bId IS NULL
SQL;

if (!($dbresult = $db->sql_query($query))) {
    die('Strange SQL error');
}
while ($row = $db->sql_fetchrow($dbresult)) {
    $db->sql_query(
        'INSERT INTO ' . $bt->getTableName() . ' '
        . $db->sql_build_array(
            'INSERT',
            array('bId' => $row['bId'], 'tag' => 'system:unfiled')
        )
    );
}
$db->sql_freeresult($dbresult);
?>