<?php
/*
To be inserted into blocks where structured tags must be displayed in a tree format.
*/

function displayLinkedTags($tag, $linkType, $uId, $cat_url, $user, $editingMode =false, $precedentTag =null, $level=0, $stopList=array()) {

    if(in_array($tag, $stopList)) {
	return array('output' => '', 'stoplist' => $stopList);
    }

    $tag2tagservice =SemanticScuttle_Service_Factory::get('Tag2Tag');
    $tagstatservice =SemanticScuttle_Service_Factory::get('TagStat');

    // link '>'
    if($level>1) {
	if($editingMode) {
	    $link = '<small><a href="'.createURL('tag2tagedit', $precedentTag.'/'.$tag).'" title="'._('Edit link').'">></a> </small>';
	} else {
	    $link = '> ';	
	}
    } else  {
	$link = '';
    }

    $output = '';
    $output.= '<tr>';
    $output.= '<td></td>';
    $output.= '<td>';
    $output.= $level ==  1?'<b>':'';
    $output.= str_repeat('&nbsp;', $level*2) .$link.'<a href="'. sprintf($cat_url, filter($user, 'url'), filter($tag, 'url')) .'" rel="tag">'. filter($tag) .'</a>';
    $output.= $level ==  1?'</b>':'';
    //$output.= ' - '. $tagstatservice->getMaxDepth($tag, $linkType, $uId);

    $synonymTags = $tag2tagservice->getAllLinkedTags($tag, '=', $uId);
    $synonymTags = is_array($synonymTags)?$synonymTags:array($synonymTags);
    sort($synonymTags);
    $synonymList = '';
    foreach($synonymTags as $synonymTag) {
	//$output.= ", ".$synonymTag;
	$synonymList.= $synonymTag.' ';
    }
    if(count($synonymTags)>0) {
        $output.= ', '.$synonymTags[0];
    }
    if(count($synonymTags)>1) {
        $output.= '<span title="'.T_('Synonyms:').' '.$synonymList.'">, etc</span>';
    }

    /*if($editingMode) {
	$output.= ' (';
	$output.= '<a href="'.createURL('tag2tagadd', $tag).'" title="'._('Add a subtag').'">+</a>';
	if(1) {
	    $output.= ' - ';
	    $output.= '<a href="'.createURL('tag2tagdelete', $tag).'">-</a>';
	}
	$output.= ')';
    }*/
    $output.= '</td>';
    $output.= '</tr>';

    $tags = array($tag);
    $tags = array_merge($tags, $synonymTags);
    foreach($tags as $tag) {

	    if(!in_array($tag, $stopList)) {
		$linkedTags = $tag2tagservice->getLinkedTags($tag, '>', $uId);
		$precedentTag = $tag;
		$stopList[] = $tag;
		foreach($linkedTags as $linkedTag) {
		    $displayLinkedTags = displayLinkedTags($linkedTag, $linkType, $uId, $cat_url, $user, $editingMode, $precedentTag, $level + 1, $stopList);
		    $output.= $displayLinkedTags['output'];
		}
		if(isset($displayLinkedTags) && is_array($displayLinkedTags['stopList'])) {
		    $stopList = array_merge($stopList, $displayLinkedTags['stopList']);
		    $stopList = array_unique($stopList);
		}
	    }

    }	
    return array('output' => $output, 'stopList' => $stopList);
}

?>
