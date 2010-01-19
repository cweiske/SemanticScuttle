<?php
/**
 * Simply create some test bookmarks
 */
require_once dirname(__FILE__) . '/../src/SemanticScuttle/header-standalone.php';

$us  = SemanticScuttle_Service_Factory::get('User');
$uid = $us->addUser('dummy', 'dummy', 'dummy@example.org');

$bs  = SemanticScuttle_Service_Factory::get('Bookmark');
for ($nA = 0; $nA < 10; $nA++) {
    $rand = rand();
    $bid  = $bs->addBookmark(
        'http://example.org/' . $rand,
        'unittest bookmark #' . $rand,
        'description',
        null,
        0,
        array('unittest'),
        null, null, false, false,
        $uid
    );
}
?>