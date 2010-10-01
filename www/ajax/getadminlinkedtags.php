<?php
/**
 * Returns a list of tags managed by the admins, in json format
 * suitable for jsTree consumption.
 *
 * @param string $tag Tag for which the children tags shall be returned
 *
 * SemanticScuttle - your social bookmark manager.
 *
 * PHP version 5.
 *
 * @category    Bookmarking
 * @package     SemanticScuttle
 * @subcategory Templates
 * @author      Benjamin Huynh-Kim-Bang <mensonge@users.sourceforge.net>
 * @author      Christian Weiske <cweiske@cweiske.de>
 * @author      Eric Dane <ericdane@users.sourceforge.net>
 * @license     GPL http://www.gnu.org/licenses/gpl.html
 * @link        http://sourceforge.net/projects/semanticscuttle
 */

$httpContentType = 'application/json';
require_once '../www-header.php';

function assembleTagData($tag, SemanticScuttle_Service_Tag2Tag $t2t)
{
    if ($tag == '') {
        $linkedTags = $GLOBALS['menu2Tags'];
    } else {
        $linkedTags = $t2t->getAdminLinkedTags($tag, '>');
    }

    $tagData = array();
    foreach ($linkedTags as $tag) {
        //FIXME: the hasChildren code is nasty, because it causes too many
        // queries onto the database
        $hasChildren = 0 < count($t2t->getAdminLinkedTags($tag, '>'));
        $tagData[] = createTagArray($tag, $hasChildren);
    }

	return $tagData;
}

function createTagArray($tag, $hasChildren = true)
{
    $ar = array(
        'data' => $tag,
        'attr' => array('rel' => $tag),
    );
    if ($hasChildren) {
        //jstree needs that to show the arrows
        $ar['state'] = 'closed';
    }

    return $ar;
}


$tag     = isset($_GET['tag']) ? trim($_GET['tag']) : '';
$tagData = assembleTagData(
    $tag,
    SemanticScuttle_Service_Factory::get('Tag2Tag')
);
echo json_encode($tagData);
?>