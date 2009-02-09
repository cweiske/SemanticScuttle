<?php
/* Service creation: only useful services are created */
$b2tservice =& ServiceFactory::getServiceInstance('Bookmark2TagService');


if(!isset($user)) $user="";


$logged_on_userid = $userservice->getCurrentUserId();
if ($logged_on_userid === false) {
    $logged_on_userid = NULL;
}

if(!isset($userid)) {
	$userid = NULL;
}

if(isset($user) && strlen($user)==0) {
    $cat_url = createURL('tags', '%2$s');
}
if ($currenttag) {
    $relatedTags = $b2tservice->getRelatedTags($currenttag, $userid, $logged_on_userid);
    if (sizeof($relatedTags) > 0) {
?>

<h2><?php echo T_('Related Tags'); ?></h2>
<div id="related">
    <table>
    <?php foreach($relatedTags as $row): ?>
    <tr>        
        <td><a href="<?php echo sprintf($cat_url, filter($user, 'url'), filter($row['tag'], 'url')); ?>" rel="tag"><?php echo filter($row['tag']); ?></a> <b><a href="<?php echo sprintf($cat_url, filter($user, 'url'), filter($currenttag, 'url') .'+'. filter($row['tag'], 'url')); ?>" title="<?php echo T_('Add this tag to the query') ?>">+</a></b></td>      
    </tr>
    <?php endforeach; ?>
    </table>
</div>

<?php
    }
}
?>
