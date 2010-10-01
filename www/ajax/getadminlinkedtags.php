<?php
/***************************************************************************
 Copyright (C) 2004 - 2006 Scuttle project
 http://sourceforge.net/projects/scuttle/
 http://scuttle.org/

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 ***************************************************************************/

/* Return a json file with list of linked tags */
$httpContentType = 'application/json';
$httpContentType='text/plain';
require_once '../www-header.php';

$tag = isset($_GET['tag']) ? trim($_GET['tag']) : '';

function assembleTagData($tag, SemanticScuttle_Service_Tag2Tag $t2t)
{
    if ($tag == '') {
        $linkedTags = $GLOBALS['menu2Tags'];
    } else {
        $linkedTags = $t2t->getAdminLinkedTags($tag, '>');
    }

    $tagData = array();
    foreach ($linkedTags as $tag) {
        $tagData[] = createTagArray($tag);
    }

	return $tagData;
}

function createTagArray($tag)
{
    return array(
        'data' => $tag,
        'attr' => array('rel' => $tag),
        //'children' => array('foo', 'bar'),
        'state' => 'closed'
    );
}


$tagData = assembleTagData(
    $tag,
    SemanticScuttle_Service_Factory::get('Tag2Tag')
);
//$json = substr($json, 0, -1); // remove final comma avoiding IE6 Dojo bug
echo json_encode($tagData);
?>