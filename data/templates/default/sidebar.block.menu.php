<?php
/* Service creation: only useful services are created */
$tag2tagservice =SemanticScuttle_Service_Factory::get('Tag2Tag');


require_once('sidebar.linkedtags.inc.php');

/* Manage input */
$userid = isset($userid)?$userid:0;
$user = isset($user)?$user:null;


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

if(!isset($user) || $user == '') {
    $cat_url = createURL('tags', '%2$s');
}

$menuTags = $tag2tagservice->getMenuTags($userid);
if (sizeof($menuTags) > 0 || ($userid != 0 && $userid === $logged_on_userid)) {
?>

<h2>
<?php
    echo '<span title="'.sprintf(T_('Tags included into the tag \'%s\''), $GLOBALS['menuTag']).'">'.T_('Menu Tags').'</span> ';
    //}
?>
</h2>


<div id="related">
<table>
<?php
    if($editingMode) {
	echo '<tr><td></td><td>';
	echo ' (<a href="'. createURL('tag2tagadd','menu') .'" rel="tag">'.T_('Add new link').'</a>) ';
	echo ' (<a href="'. createURL('tag2tagdelete','menu') .'" rel="tag">'.T_('Delete link').'</a>)';
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

<?php $cUser = $userservice->getUser($userid); ?>
<?php if($userid>0): ?>
<?php if($userid==$logged_on_userid): ?>
<p style="text-align:right"><a href="<?php echo createURL('alltags', $cUser['username']); ?>" title="<?php echo T_('See all your tags')?>"><?php echo T_('all your tags'); ?></a> →</p>
<?php else: ?>
<p style="text-align:right"><a href="<?php echo createURL('alltags', $cUser['username']); ?>" title="<?php echo T_('See all tags from this user')?>"><?php echo T_('all tags from this user'); ?></a> →</p>
<?php endif; ?>

<?php else : ?>
<p style="text-align:right"><a href="<?php echo createURL('populartags', $cUser['username']); ?>" title="<?php echo T_('See popular tags')?>"><?php echo T_('Popular Tags'); ?></a> →</p>
<?php endif; ?>
</div>

<?php
}
?>
