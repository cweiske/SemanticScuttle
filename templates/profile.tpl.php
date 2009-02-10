<?php
$this->includeTemplate($GLOBALS['top_include']);
?>

<dl id="profile">
<dt><?php echo T_('Username'); ?></dt>
    <dd><?php echo $user; ?></dd>
<?php
if ($userservice->isLoggedOn() && $currentUser->isAdmin()) {
?>
<dt><?php echo T_('Email'); ?></dt>
    <dd><?php echo filter($objectUser->getEmail()) ?></dd>    
<?php
}
if ($objectUser->getName() != "") {
?>
<dt><?php echo T_('Name'); ?></dt>
    <dd><?php echo filter($objectUser->getName()); ?></dd>
<?php
}
if ($objectUser->getHomepage() != "") {
?>
<dt><?php echo T_('Homepage'); ?></dt>
    <dd><a href="<?php echo filter($objectUser->getHomepage()); ?>"><?php echo filter($objectUser->getHomepage()); ?></a></dd>
<?php
}
?>
<dt><?php echo T_('Member Since'); ?></dt>
    <dd><?php echo date($GLOBALS['longdate'], strtotime($objectUser->getDatetime())); ?></dd>
<?php
if ($objectUser->getContent() != "") {
?>
<dt><?php echo T_('Description'); ?></dt>
    <dd><?php echo $objectUser->getContent(); ?></dd>
<?php
}
$watching = $userservice->getWatchNames($userid);
if ($watching) {
?>
    <dt><?php echo T_('Watching'); ?></dt>
        <dd>
            <?php
            $list = '';
            foreach($watching as $watchuser) {
                $list .= '<a href="'. createURL('bookmarks', $watchuser) .'">'. $watchuser .'</a>, ';
            }
            echo substr($list, 0, -2);
            ?>
        </dd>
<?php
}
$watchnames = $userservice->getWatchNames($userid, true);
if ($watchnames) {
?>
    <dt><?php echo T_('Watched By'); ?></dt>
        <dd>
            <?php
            $list = '';
            foreach($watchnames as $watchuser) {
                $list .= '<a href="'. createURL('bookmarks', $watchuser) .'">'. $watchuser .'</a>, ';
            }
            echo substr($list, 0, -2);
            ?>
        </dd>
<?php
}
?>
<dt><?php echo T_('Bookmarks'); ?></dt>
    <dd><a href="<?php echo createURL('bookmarks', $user) ?>"><?php echo T_('Go to bookmarks')?> >></a></dd>
</dl>

<?php
$this->includeTemplate($GLOBALS['bottom_include']);
?>
