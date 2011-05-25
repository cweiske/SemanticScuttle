<?php
/* Service creation: only useful services are created */
//No specific services


if ($userservice->isLoggedOn()) {

    if ($currentUser->getUsername() != $user) {
        $result = $userservice->getWatchStatus($userid, $userservice->getCurrentUserId());
        if ($result) {
            $linkText = T_('Remove from Watchlist');
        } else {
            $linkText = T_('Add to Watchlist');
        }
        $linkAddress = createURL('watch', $user);
?>

<h2><?php echo T_('Actions'); ?></h2>
<div id="watchlist">
    <ul>
        <li><a href="<?php echo $linkAddress ?>"><?php echo $linkText ?></a></li>
    </ul>
</div>

<?php
    }
}
?>