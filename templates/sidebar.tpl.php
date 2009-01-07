<?php if($GLOBALS['enableAdminColors']!=false && isset($userid) && $userservice->isAdmin($userid)): ?>
<div id="sidebar" class="adminBackground">
<?php else: ?>
<div id="sidebar">
<?php endif ?>


<?php

$size = count($sidebar_blocks);
for ($i = 0; $i < $size; $i++) {
	$this->includeTemplate('sidebar.block.'. $sidebar_blocks[$i]);
}
?>

</div>