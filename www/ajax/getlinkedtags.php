<?php
/**
 * Returns a list of tags linked to the given one(s),
 * suitable for jsTree consumption.
 *
 * Accepted GET parameters:
 *
 * @param string  $tag    Tag for which the children tags shall be returned
 *                        Multiple tags (separated with comma) are supported.
 *                        If no tag is given, all top-level tags are loaded.
 * @param integer $uId    User ID to fetch the tags for
 * @param boolean $parent Load parent tags
 *
 * SemanticScuttle - your social bookmark manager.
 *
 * PHP version 5.
 *
 * @category   Bookmarking
 * @package    SemanticScuttle
 * @subpackage Templates
 * @author     Benjamin Huynh-Kim-Bang <mensonge@users.sourceforge.net>
 * @author     Christian Weiske <cweiske@cweiske.de>
 * @author     Eric Dane <ericdane@users.sourceforge.net>
 * @license    GPL http://www.gnu.org/licenses/gpl.html
 * @link       http://sourceforge.net/projects/semanticscuttle
 */
$httpContentType = 'application/json';
require_once '../www-header.php';

$tag            = isset($_GET['tag']) ? $_GET['tag'] : null;
$uId            = isset($_GET['uId']) ? (int)$_GET['uId'] : 0;
$loadParentTags = isset($_GET['parent']) ? (bool)$_GET['parent'] : false;

$tags = explode(',', trim($tag));
if (count($tags) == 1 && $tags[0] == '') {
    //no tags
    $tags = array();
}


function assembleLinkedTagData(
    $tags, $uId, $loadParentTags, SemanticScuttle_Service_Tag2Tag $t2t
) {
    $tagData = array();

    if (count($tags) == 0) {
        //no tags given -> show the 4 most used top-level tags
        $orphewTags     = $t2t->getOrphewTags('>', $uId, 4, 'nb');
        #$orphewTags = $t2t->getOrphewTags('>', $uId);
        foreach ($orphewTags as $orphewTag) {
            $tags[] = $orphewTag['tag'];
        }
        $loadParentTags = true;
    }

    if ($loadParentTags) {
        //find parent tags + append the selected tags as children afterwards
        foreach ($tags as $tag) {
            $parentTags = $t2t->getLinkedTags($tag, '>', $uId, true);
            if (count($parentTags) > 0) {
                foreach ($parentTags as $parentTag) {
                    $ta = createTagArray(
                        $parentTag, true, true, true
                    );
                    //FIXME: find out if there are subtags
                    $tac = createTagArray($tag, true);
                    $ta['children'][] = $tac;
                    $tagData[] = $ta;
                }
            } else {
                //no parent tags -> display it normally
                //FIXME: find out if there are subtags
                $tagData[] = createTagArray($tag, true);
            }
        }
    } else {
        //just find the linked tags
        foreach ($tags as $tag) {
            $linkedTags = $t2t->getLinkedTags($tag, '>', $uId);
            foreach ($linkedTags as $linkedTag) {
                //FIXME: find out if there are subtags
                $tagData[] = createTagArray($linkedTag, true);
            }
        }
    }

    return $tagData;
}

/**
 * Creates an jsTree json array for the given tag
 *
 * @param string  $tag         Tag name
 * @param boolean $hasChildren If the tag has subtags (children) or not.
 *                             If unsure, set it to "true".
 * @param boolean $isOpen      If the tag has children: Is the tree node open
 *                             or closed?
 * @param boolean $autoParent  If the tag is an automatically generated parent tag
 *
 * @return array Array to be sent back to the browser as json
 */
function createTagArray($tag, $hasChildren = true, $isOpen = false, $autoParent = false)
{
    if ($autoParent) {
        $title = '(' . $tag . ')';
    } else {
        $title = $tag;
    }

    $ar = array(
        'data' => array(
            //<a> attributes
            'title' => $title,
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
        $ar['state'] = $isOpen ? 'open' : 'closed';
    }
    if ($autoParent) {
        //FIXME: use css class
        $ar['data']['attr']['style'] = 'color: #AAA';
    }

    return $ar;
}


$tagData = assembleLinkedTagData(
    $tags, 0/*$uId*/, $loadParentTags,
    SemanticScuttle_Service_Factory::get('Tag2Tag')
);
echo json_encode($tagData);
?>