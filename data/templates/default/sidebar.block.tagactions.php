<?php
/* Service creation: only useful services are created */
//No specific services

if ($userservice->isLoggedOn()) {

    if ($currentUser->getUsername() == $user) {
        $tags = explode('+', $currenttag);
        $renametext = T_ngettext('Rename Tag', 'Rename Tags', count($tags));
        $renamelink = createURL('tagrename', $currenttag);
        $deletelink = createURL('tagdelete', $currenttag);
        $tagdesclink = createURL('tagedit', $currenttag);
        $commondesclink = createURL('tagcommondescriptionedit', $currenttag);
        $addtag2taglinklink = createURL('tag2tagadd', $currenttag);
?>

<h2><?php echo T_('Actions'); ?></h2>
<div id="tagactions">
    <ul>
        <li><a href="<?php echo $renamelink; ?>"><?php echo $renametext ?></a></li>
        <?php if (count($tags) == 1): ?>
        <li><a href="<?php echo $deletelink; ?>"><?php echo T_('Delete Tag') ?></a></li>
        <?php endif; ?>
        <li><a href="<?php echo $tagdesclink; ?>"><?php echo T_('Edit Tag Description') ?></a></li>
        <?php if ($GLOBALS['enableCommonTagDescription'] && ($GLOBALS['enableCommonTagDescriptionEditedByAll'] || $currentUser->isAdmin() )): ?>
        <li><a href="<?php echo $commondesclink; ?>"><?php echo T_('Edit Tag Common Description') ?></a></li>
	<?php endif; ?>
        <li><a href="<?php echo $addtag2taglinklink; ?>"><?php echo T_('Create a link to another tag') ?></a></li>
    </ul>
</div>

<?php
    }
}
?>
