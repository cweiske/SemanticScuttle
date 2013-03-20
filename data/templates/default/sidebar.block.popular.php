<?php
/* Service creation: only useful services are created */
$b2tservice =SemanticScuttle_Service_Factory::get('Bookmark2Tag');


if(!isset($user)) {
	$user = '';
}
if(!isset($userid)) {
	$userid = NULL;
}

$logged_on_userid = $userservice->getCurrentUserId();
if ($logged_on_userid === false) {
    $logged_on_userid = NULL;
}
$popularTags = $b2tservice->getPopularTags($userid, $popCount, $logged_on_userid);
$popularTags = $b2tservice->tagCloud($popularTags, 5, 90, 225, 'alphabet_asc');

if ($popularTags && count($popularTags) > 0) {
?>

<h2><?php echo T_('Popular Tags'); ?></h2>
<div id="popular">
    <p class="tags">
    <?php
    $contents = '';
    
    if(strlen($user)==0) {
	$cat_url = createURL('tags', '%2$s');
    }

    foreach ($popularTags as $row) {
        $entries = T_ngettext('bookmark', 'bookmarks', $row['bCount']);
        $contents .= '<a href="'. sprintf($cat_url, $user, filter($row['tag'], 'url')) .'" title="'. $row['bCount'] .' '. $entries .'" rel="tag" style="font-size:'. $row['size'] .'">'. filter($row['tag']) .'</a> ';
    }
    echo $contents ."\n";
    ?>
    </p>
</div>

<?php
}
?>
