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

/**
 * Creates and returns an array of tags for the jsTree ajax loader.
 * If the tag is empty, the configured menu2 (admin) main tags are used.
 *
 * @param string                          $tag Tag name to fetch subtags for
 * @param SemanticScuttle_Service_Tag2Tag $t2t Tag relation service
 *
 * @return array Array of tag data suitable for the jsTree ajax loader
 */
function assembleAdminTagData($tag, SemanticScuttle_Service_Tag2Tag $t2t)
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

/**
 * Creates an jsTree json array for the given tag
 *
 * @param string  $tag         Tag name
 * @param boolean $hasChildren If the tag has subtags (children) or not.
 *                             If unsure, set it to "true".
 *
 * @return array Array to be sent back to the browser as json
 */
function createTagArray($tag, $hasChildren = true)
{
    $ar = array(
        'data' => array(
            //<a> attributes
            'title' => $tag,
            'attr' => array(
                'href' => createUrl('tags', $tag)
            )
        ),
        //<li> attributes
        'attr' => array(
            'rel'  => $tag,//needed for identifying the tag in html
        ),
    );
    if ($hasChildren) {
        //jstree needs that to show the arrows
        $ar['state'] = 'closed';
    }

    return $ar;
}


$tag     = isset($_GET['tag']) ? trim($_GET['tag']) : '';
$tagData = assembleAdminTagData(
    $tag,
    SemanticScuttle_Service_Factory::get('Tag2Tag')
);
echo json_encode($tagData);
?>