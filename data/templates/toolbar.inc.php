<?php
if ($userservice->isLoggedOn() && is_object($currentUser)) {
    $cUserId = $userservice->getCurrentUserId();
    $cUsername = $currentUser->getUsername();
?>

    <ul id="navigation">
    	<li><a href="<?php echo createURL(''); ?>"><?php echo T_('Home'); ?></a></li>    
        <li><a href="<?php echo createURL('bookmarks', $cUsername); ?>"><?php echo T_('Bookmarks'); ?></a></li>
	<li><a href="<?php echo createURL('alltags', $cUsername); ?>"><?php echo T_('Tags'); ?></a></li>
        <li><a href="<?php echo createURL('watchlist', $cUsername); ?>"><?php echo T_('Watchlist'); ?></a></li>
	<li><a href="<?php echo $userservice->getProfileUrl($cUserId, $cUsername); ?>"><?php echo T_('Profile'); ?></a></li>
        <li><a href="<?php echo createURL('bookmarks', $cUsername . '?action=add'); ?>"><?php echo T_('Add a Bookmark'); ?></a></li>
        <li class="access"><?php echo $cUsername?><a href="<?php echo ROOT ?>?action=logout">(<?php echo T_('Log Out'); ?>)</a></li>
        <li><a href="<?php echo createURL('about'); ?>"><?php echo T_('About'); ?></a></li>
	<?php if($currentUser->isAdmin()): ?>
        <li><a href="<?php echo createURL('admin', ''); ?>"><?php echo '['.T_('Admin').']'; ?></a></li>
	<?php endif; ?>

    </ul>

<?php
} else {
?>
    <ul id="navigation">
    	<li><a href="<?php echo createURL(''); ?>"><?php echo T_('Home'); ?></a></li>
	<li><a href="<?php echo createURL('populartags'); ?>"><?php echo T_('Popular Tags'); ?></a></li>
        <li><a href="<?php echo createURL('about'); ?>"><?php echo T_('About'); ?></a></li>
        <li class="access"><a href="<?php echo createURL('login'); ?>"><?php echo T_('Log In'); ?></a></li>
        <?php if ($GLOBALS['enableRegistration']) { ?>
        <li class="access"><a href="<?php echo createURL('register'); ?>"><?php echo T_('Register'); ?></a></li>
        <?php } ?>
    </ul>

<?php
}
?>
