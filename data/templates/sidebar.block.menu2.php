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


$cat_url = createURL('tags', '%2$s');
$menu2Tags = $GLOBALS['menu2Tags'];

if (sizeOf($menu2Tags) > 0) {
	$this->includeTemplate("dojo.inc");
	?>

<h2><?php echo T_('Featured Menu Tags');?></h2>


<div id="maintagsmenu"
<?php echo 'title="'.T_('This menu is composed of keywords (tags) organized by admins.').'"'?>>

<?php
foreach($menu2Tags as $menu2Tag) {

	echo '<div dojoType="dojo.data.ItemFileReadStore" url="'.ROOT.'ajax/getadminlinkedtags.php?tag='.filter($menu2Tag, 'url').'" jsid="linkedTagStore" ></div>';
	echo '<div dojoType="dijit.Tree" store="linkedTagStore" labelAttr="name" >';
	echo '<script type="dojo/method" event="onClick" args="item">';
	$returnUrl = sprintf($cat_url, filter($user, 'url'), filter('', 'url'));
	echo 'window.location = "'.$returnUrl.'"+item.name';
	echo '</script>';
	//echo '<script type="dojo/method" event="getLabel" args="item">';
	//echo 'return item.name + "...";';
	//echo '</script>';
	//echo '<script type="dojo/method" event="onMouseOver" args="item">';
	//echo 'i = item.relatedTarget;';
	//echo 'if(i.innerHTML.charAt(i.innerHTML)=="a") alert(i.innerHTML)';
	//echo '</script>';
	//echo '<script type="dojo/method" event="getLabelClass" args="item">';
	//echo 'return \'treeTag\';';
	//echo '</script>';
	echo '</div>';
}
?>
</div>


<?php
}
?>
