<?php
$tag2tagservice =& ServiceFactory::getServiceInstance('Tag2TagService');
$userservice =& ServiceFactory::getServiceInstance('UserService');

function displayLinkedTags($tag, $linkType, $uId, $cat_url, $user, $editingMode =false, $precedentTag =null, $level=0, $stopList=array()) {

    if(in_array($tag, $stopList)) {
	return array('output' => '', 'stoplist' => $stopList);
    }

    $tag2tagservice =& ServiceFactory::getServiceInstance('Tag2TagService');
    $tagstatservice =& ServiceFactory::getServiceInstance('TagStatService');

    // link '>'
    if($level>1) {
	if($editingMode) {
	    $link = '<small><a href="'.createURL('tag2tagedit', $precedentTag.'/'.$tag).'" title="'._('Edit link').'">></a> </small>';
	} else {
	    $link = '> ';	
	}
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
		if(is_array($displayLinkedTags['stopList'])) {
		    $stopList = array_merge($stopList, $displayLinkedTags['stopList']);
		    $stopList = array_unique($stopList);
		}
	    }

    }	
    return array('output' => $output, 'stopList' => $stopList);
}

$logged_on_userid = $userservice->getCurrentUserId();
if ($logged_on_userid === false) {
    $logged_on_userid = NULL;
}

$explodedTags = array();
if ($currenttag) {
    $explodedTags = explode('+', $currenttag);
} else {
    if($summarizeLinkedTags == true) {
	$orphewTags = $tag2tagservice->getOrphewTags('>', $userid, 4, "nb");
    } else {
        $orphewTags = $tag2tagservice->getOrphewTags('>', $userid);
    }

    foreach($orphewTags as $orphewTag) {
	$explodedTags[] = $orphewTag['tag'];
    }
}

?>

<h2>
<?php
    echo T_('Linked Tags').' ';
    //if($userid != null) {
	$cUser = $userservice->getUser($userid);
	echo '<a href="'.createURL('alltags', $cUser['username']).'">('.T_('plus').')</a>';
    //}
?>
</h2>

<?php if(count($explodedTags)>0):?>

<div id="linked">
    <table>
    <?php
	if(($logged_on_userid != null) && ($userid === $logged_on_userid)) {
	    $editingMode = true;
	} else {
	    $editingMode = false;
	}

	if($editingMode) {
	    echo '<tr><td></td><td>';
	    echo ' (<a href="'. createURL('tag2tagadd','') .'" rel="tag">'.T_('Add new link').'</a>) ';
	    echo ' (<a href="'. createURL('tag2tagdelete','') .'" rel="tag">'.T_('Delete link').'</a>)';
	    echo '</td></tr>';
	}

	if(strlen($user)==0) {
	    $cat_url = createURL('tags', '%2$s');
	}

	$stopList = array();
	foreach($explodedTags as $explodedTag) {
	    if(!in_array($explodedTag, $stopList)) {
		// fathers tag
		$fatherTags = $tag2tagservice->getLinkedTags($explodedTag, '>', $userid, true);
		if(count($fatherTags)>0) {
		    foreach($fatherTags as $fatherTag) {
			echo '<tr><td></td><td>';
			echo '<a href="'. sprintf($cat_url, filter($user, 'url'), filter($fatherTag, 'url')) .'" rel="tag">('. filter($fatherTag) .')</a>';
			echo '</td></tr>';
		    }
		}

		$displayLinkedTags = displayLinkedTags($explodedTag, '>', $userid, $cat_url, $user, $editingMode, null, 1);
		echo $displayLinkedTags['output'];
		if(is_array($displayLinkedTags['stopList'])) {
	    	    $stopList = array_merge($stopList, $displayLinkedTags['stopList']);
		}
	    }

	}
    ?>
    </table>
</div>

<?php endif?>
