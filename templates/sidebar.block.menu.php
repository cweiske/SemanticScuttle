<?php
$tag2tagservice =& ServiceFactory::getServiceInstance('Tag2TagService');
$userservice =& ServiceFactory::getServiceInstance('UserService');

require_once('./sidebar.linkedtags.inc.php');

$logged_on_userid = $userservice->getCurrentUserId();
if ($logged_on_userid === false) {
    $logged_on_userid = NULL;
}

// editing mode
if(($logged_on_userid != null) && ($userid === $logged_on_userid)) {
    $editingMode = true;
} else {
    $editingMode = false;
}

if(strlen($user)==0) {
    $cat_url = createURL('tags', '%2$s');
}

$menuTags = $tag2tagservice->getMenuTags($userid);
if (sizeof($menuTags) > 0) {
?>

<h2>
<?php
    echo '<span title="'.sprintf(T_('Tags included into the tag \'%s\''), $GLOBALS['menuTag']).'">'.T_('Menu Tags').'</span> ';
    $cUser = $userservice->getUser($userid);
    echo '<small span title="'.T_('See all tags').'"><a href="'.createURL('alltags', $cUser['username']).'">('.T_('all tags').')</a></small>';
    //}
?>
</h2>


<div id="related">
<table>
<?php
    if($editingMode) {
	echo '<tr><td></td><td>';
	echo ' (<a href="'. createURL('tag2tagadd','') .'" rel="tag">'.T_('Add new link').'</a>) ';
	echo ' (<a href="'. createURL('tag2tagdelete','') .'" rel="tag">'.T_('Delete link').'</a>)';
	echo '</td></tr>';
    }

    $stopList = array();
    foreach($menuTags as $menuTag) {
	$tag = $menuTag['tag'];
	if(!in_array($tag, $stopList)) {
	    $displayLinkedTags = displayLinkedTags($tag, '>', $userid, $cat_url, $user, $editingMode, null, 1);
	    echo $displayLinkedTags['output'];
	    if(is_array($displayLinkedTags['stopList'])) {
		$stopList = array_merge($stopList, $displayLinkedTags['stopList']);
	    }
	}
    }
?>
</table>
</div>

<?php
}
?>
