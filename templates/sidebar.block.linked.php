<?php
$tag2tagservice =& ServiceFactory::getServiceInstance('Tag2TagService');
$userservice =& ServiceFactory::getServiceInstance('UserService');

require_once('./sidebar.linkedtags.inc.php');

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

<?php
if(($logged_on_userid != null) && ($userid === $logged_on_userid)) {
    $editingMode = true;
} else {
    $editingMode = false;
}
?>

<?php if(count($explodedTags)>0 || $editingMode):?>

<h2>
<?php
    echo T_('Linked Tags').' ';
    //if($userid != null) {
	$cUser = $userservice->getUser($userid);
	echo '<small><a href="'.createURL('alltags', $cUser['username']).'">('.T_('all tags').')</a></small>';
    //}
?>
</h2>
<?php //endif?>

<div id="linked">
    <table>
    <?php
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
