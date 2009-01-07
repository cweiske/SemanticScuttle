<?php
/* Service creation: only useful services are created */
$tag2tagservice =& ServiceFactory::getServiceInstance('Tag2TagService');

require_once('sidebar.linkedtags.inc.php');

/* Manage input */
$user = isset($user)?$user:'';
$userid = isset($userid)?$userid:0;
$currenttag = isset($currenttag)?$currenttag:'';
$summarizeLinkedTags = isset($summarizeLinkedTags)?$summarizeLinkedTags:false;


$logged_on_userid = $userservice->getCurrentUserId();
if ($logged_on_userid === false) {
	$logged_on_userid = NULL;
}

$explodedTags = array();
if (strlen($currenttag)>0) {
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

$this->includeTemplate("dojo.inc");
?>

<?php if(count($explodedTags)>0 || $editingMode):?>

<h2><?php


echo T_('Linked Tags').' ';
//if($userid != null) {
$cUser = $userservice->getUser($userid);
//echo '<small><a href="'.createURL('alltags', $cUser['username']).'">('.T_('all tags').')</a></small>';
//}
?></h2>
<?php //endif?>

<div id="related"><!-- table--> <?php
if($editingMode) {
	//echo '<tr><td></td><td>';
	echo '<p style="margin-bottom: 13px;text-align:center;">';
	echo ' (<a href="'. createURL('tag2tagadd','') .'" rel="tag">'.T_('Add new link').'</a>) ';
	echo ' (<a href="'. createURL('tag2tagdelete','') .'" rel="tag">'.T_('Delete link').'</a>)';
	echo '</p>';
	//echo '</td></tr>';
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
				//echo '<tr><td></td><td>';
				echo '<a href="'. sprintf($cat_url, filter($user, 'url'), filter($fatherTag, 'url')) .'" rel="tag">('. filter($fatherTag) .')</a> ';
				//echo '</td></tr>';
			}
		}
		/*
		 $displayLinkedTags = displayLinkedTags($explodedTag, '>', $userid, $cat_url, $user, $editingMode, null, 1);
		 echo $displayLinkedTags['output'];
		 if(is_array($displayLinkedTags['stopList'])) {
		 $stopList = array_merge($stopList, $displayLinkedTags['stopList']);
		 }*/
		echo '<div dojoType="dojo.data.ItemFileReadStore" url="'.ROOT.'ajax/getlinkedtags.php?tag='.$explodedTag.'&uId='.$userid.'" jsid="linkedTagStore" ></div>';
		echo '<div dojoType="dijit.Tree" store="linkedTagStore" labelAttr="name" >';
		echo '<script type="dojo/method" event="onClick" args="item">';
		$returnUrl = sprintf($cat_url, filter($user, 'url'), filter('', 'url'));
		echo 'window.location = "'.$returnUrl.'"+item.name';
		echo '</script>';
		echo '<script type="dojo/method" event="getLabelClass" args="item">';
		echo 'return \'treeTag\';';
		echo '</script>';
		echo '</div>';
	}

}
?> <!-- /table--></div>

<?php endif?>
