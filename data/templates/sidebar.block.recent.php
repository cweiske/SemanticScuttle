<?php
/* Service creation: only useful services are created */
$b2tservice =SemanticScuttle_Service_Factory::get('Bookmark2Tag');

/* Manage input */
$userid = isset($userid)?$userid:NULL;

$logged_on_userid = $userservice->getCurrentUserId();
if ($logged_on_userid === false) {
    $logged_on_userid = NULL;
}
$recentTags = $b2tservice->getPopularTags($userid, $popCount, $logged_on_userid, $GLOBALS['defaultRecentDays']);
$recentTags =& $b2tservice->tagCloud($recentTags, 5, 90, 225, 'alphabet_asc'); 

if ($recentTags && count($recentTags) > 0) {
?>

<h2><?php echo T_('Recent Tags'); ?></h2>
<div id="recent">
    <?php
    $contents = '<p class="tags">';

    if(!isset($user) || $user == '') {
    	$user = '';
		$cat_url = createURL('tags', '%2$s');
    }

    foreach ($recentTags as $row) {    	
        $entries = T_ngettext('bookmark', 'bookmarks', $row['bCount']);
        $contents .= '<a href="'. sprintf($cat_url, $user, filter($row['tag'], 'url')) .'" title="'. $row['bCount'] .' '. $entries .'" rel="tag" style="font-size:'. $row['size'] .'">'. filter($row['tag']) .'</a> ';
    }
    echo $contents ."</p>\n";
    ?>
    <p style="text-align:right"><a href="<?php echo createURL('populartags'); ?>"><?php echo T_('Popular Tags'); ?></a> →</p>
</div>

<?php
}
?>
