<?php
$tag2tagservice =& ServiceFactory::getServiceInstance('Tag2TagService');
$userservice =& ServiceFactory::getServiceInstance('UserService');

function displayLinkedTags($tag, $linkType, $uId, $cat_url, $user, $editingMode =false, $precedentTag =null, $level=0, $stopList=array()) {
    $tag2tagservice =& ServiceFactory::getServiceInstance('Tag2TagService');

    $output = '';
    $output.= '<tr>';
    $output.= '<td></td>';
    $output.= '<td>'. str_repeat('&nbsp;', $level*2) .'<a href="'. sprintf($cat_url, filter($user, 'url'), filter($tag, 'url')) .'" rel="tag">'. filter($tag) .'</a>';
    if($editingMode) {
	$output.= ' (';
	$output.= '<a href="'.createURL('tag2tagadd', $tag).'">add</a>';
	if($precedentTag != null) {
	    $output.= ' - ';
	    $output.= '<a href="'.createURL('tag2tagdelete', $precedentTag.'/'.$tag).'">del</a>';
	}
	$output.= ')';
    }
    $output.= '</td>';
    $output.= '</tr>';

    if(!in_array($tag, $stopList)) {
	$linkedTags = $tag2tagservice->getLinkedTags($tag, '>', $userid, $level);
	$precedentTag = $tag;
	$stopList[] = $tag;
	$level = $level + 1;
	foreach($linkedTags as $linkedTag) {
	    $output.= displayLinkedTags($linkedTag, $linkType, $uId, $cat_url, $user, $editingMode, $precedentTag, $level, $stopList);
        }
    }
    return $output;
}


$logged_on_userid = $userservice->getCurrentUserId();
if ($logged_on_userid === false) {
    $logged_on_userid = NULL;
}

$explodedTags = array();
if ($currenttag) {
    $explodedTags = explode('+', $currenttag);
} else {
    $orphewTags = $tag2tagservice->getOrphewTags('>', $userid);
    foreach($orphewTags as $orphewTag) {
	$explodedTags[] = $orphewTag['tag'];
    }
}

if(count($explodedTags) > 0) {
    $displayLinkedZone = false;
    foreach($explodedTags as $explodedTag) {
	if($tag2tagservice->getLinkedTags($explodedTag, '>', $userid)) {
	    $displayLinkedZone = true;	  
	    break;
	}
    }
    if ($displayLinkedZone) {
?>

<h2><?php echo T_('Linked Tags'); ?></h2>
<div id="linked">
    <table>
    <?php
	$editingMode = ($userid === $logged_on_userid);
	foreach($explodedTags as $explodedTag) {
		echo displayLinkedTags($explodedTag, '>', $userid, $cat_url, $user, $editingMode);
	}
    ?>
    </table>
</div>

<?php
    }
}
?>
