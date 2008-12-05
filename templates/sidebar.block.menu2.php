<?php
/* Service creation: only useful services are created */
$tag2tagservice =& ServiceFactory::getServiceInstance('Tag2TagService');

require_once('sidebar.linkedtags.inc.php');

/* Manage input */
$userid = isset($userid)?$userid:0;
$user = isset($user)?$user:null;


$logged_on_userid = $userservice->getCurrentUserId();
if ($logged_on_userid === false) {
	$logged_on_userid = NULL;
}

if(!isset($user)  || $user == '') {
	$cat_url = createURL('tags', '%2$s');
}

$menu2Tags = $GLOBALS['menu2Tags'];

if (sizeOf($menu2Tags) > 0) {
	$this->includeTemplate("dojo.inc");
	?>

<h2><?php echo '<span>'.T_('Menu Tags').'</span> ';?></h2>


<div id="related"><?php
foreach($menu2Tags as $menu2Tag) {

	echo '<div dojoType="dojo.data.ItemFileReadStore" url="ajax/getlinkedtags.php?tag='.$menu2Tag.'" jsid="linkedTagStore" ></div>';
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
?> <!-- /table--> <?php $cUser = $userservice->getUser($userid); ?> <?php if($userid>0): ?>
<?php if($userid==$logged_on_userid): ?>
<p style="text-align: right"><a
	href="<?php echo createURL('alltags', $cUser['username']); ?>"
	title="<?php echo T_('See all your tags')?>"><?php echo T_('all your tags'); ?></a>
&rarr;</p>
<?php else: ?>
<p style="text-align: right"><a
	href="<?php echo createURL('alltags', $cUser['username']); ?>"
	title="<?php echo T_('See all tags from this user')?>"><?php echo T_('all tags from this user'); ?></a>
&rarr;</p>
<?php endif; ?> <?php else : ?>
<p style="text-align: right"><a
	href="<?php echo createURL('populartags', $cUser['username']); ?>"
	title="<?php echo T_('See popular tags')?>"><?php echo T_('Popular Tags'); ?></a>
&rarr;</p>
<?php endif; ?></div>

<?php
}
?>
