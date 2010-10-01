<script type="text/javascript" src="<?php echo ROOT ?>js/jquery-1.4.2.js"></script>
<script type="text/javascript" src="<?php echo ROOT ?>js/jquery.jstree.js"></script>
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


$cat_url = createURL('tags', '%s');
$menu2Tags = $GLOBALS['menu2Tags'];

if (count($menu2Tags) > 0) {
?>

<h2><?php echo T_('Featured Menu Tags');?></h2>


<div id="maintagsmenu"
<?php echo 'title="'.T_('This menu is composed of keywords (tags) organized by admins.').'"'?>>
 <ul>
<?php
foreach ($menu2Tags as $menu2Tag) {
    echo '  <li>'
        . sprintf(
            '<a href="%s">%s</a><ul><li><a href="#">foo</a></li><li><a href="#">bar</a></li></ul>',
            sprintf($cat_url, $menu2Tag),
            $menu2Tag
        )
        . '</li>';
    /*    
	echo '<div dojoType="dojo.data.ItemFileReadStore" url="'.ROOT.'ajax/getadminlinkedtags.php?tag='.filter($menu2Tag, 'url').'" jsid="linkedTagStore" ></div>';
	echo '<div dojoType="dijit.Tree" store="linkedTagStore" labelAttr="name" >';
	echo '<script type="dojo/method" event="onClick" args="item">';
	$returnUrl = sprintf($cat_url, filter($user, 'url'), filter('', 'url'));
	echo 'window.location = "'.$returnUrl.'"+item.name';
	echo '</script>';
	echo '</div>';
    */
}
//ROOT.'ajax/getadminlinkedtags.php?tag='.filter($menu2Tag, 'url')
?>
 </ul>
</div>
<script type="text/javascript">
jQuery("#maintagsmenu")
.jstree({
    "themes" : {
        "theme": "default",
        "dots": false,
        "icons": true,
        "url": '<?php echo ROOT ?>js/themes/default/style.css'
    },
    "json_data" : {
        "ajax" : {
            "url": function(node) {
                //-1 is root
                if (node == -1 ) {
                    node = "";
                } else if (node.attr('rel')) {
                    node = node.attr('rel');
                } else {
                    return;
                }
                return "<?php echo ROOT ?>ajax/getadminlinkedtags.php?tag=" + node;
            }
        }
    },
    plugins : [ "themes", "json_data"]
});
</script>
<?php
}
?>
