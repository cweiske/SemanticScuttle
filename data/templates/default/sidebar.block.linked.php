<?php
/*
 * Used in:
 * - populartags.php
 * - bookmarks.php
 * - alltags.php
 * - tags.php
 */
/* Service creation: only useful services are created */
$tag2tagservice =SemanticScuttle_Service_Factory::get('Tag2Tag');

require_once('sidebar.linkedtags.inc.php');

/* Manage input */
$user = isset($user)?$user:'';
$userid = isset($userid)?$userid:0;
$currenttag = isset($currenttag) ? str_replace('+', ',', $currenttag) : '';
//$summarizeLinkedTags = isset($summarizeLinkedTags)?$summarizeLinkedTags:false;

$logged_on_userid = $userservice->getCurrentUserId();
$editingMode = $logged_on_userid !== false;
?>
<h2><?php echo T_('Linked Tags'); ?></h2>
<div id="related">
<?php
if ($editingMode) {
	echo '<p style="margin-bottom: 13px;text-align:center;">';
	echo ' (<a href="'. createURL('tag2tagadd','') .'" rel="tag">'.T_('Add new link').'</a>) ';
	echo ' (<a href="'. createURL('tag2tagdelete','') .'" rel="tag">'.T_('Delete link').'</a>)';
	echo '</p>';
}
?>
 <div id="related-content"></div>
 <script type="text/javascript">//<![CDATA[
jQuery("#related-content")
.jstree({
    "themes" : {
        "theme": "default",
        "dots": false,
        "icons": true,
        "url": '<?php echo ROOT_JS ?>themes/default/style.css'
    },
    "json_data" : {
        "ajax" : {
            "url": function(node) {
                //-1 is root
                parentparam = "";
                if (node == -1 ) {
                    node = <?php echo json_encode($currenttag); ?>;
                    parentparam = "&parent=true";
                } else if (node.attr('rel')) {
                    node = node.attr('rel');
                } else {
                    return;
                }

                return "<?php echo ROOT ?>ajax/getlinkedtags.php?tag=" + node
                    + parentparam;
            }
        }
    },
    plugins : [ "themes", "json_data"]
});
//]]>
 </script>
</div>