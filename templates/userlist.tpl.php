<?php

/* Service creation: only useful services are created */
$userservice =& ServiceFactory::getServiceInstance('UserService');

$currentObjectUser = $userservice->getCurrentObjectUser();

$this->includeTemplate($GLOBALS['top_include']);

echo '<ol id="bookmarks">';

foreach($users as $user) {
	echo '<li class="xfolkentry">'."\n";

	echo '<div class="link">';
	echo '<a href="'.createURL('profile', $user->getUsername()).'">'.$user->getUsername().'</a>';
	echo '</div>';

	if($user->getUsername() != $currentObjectUser->getUsername()) {
	    echo '<div class="meta">';
	    echo '<a href="'.createURL('admin','delete/'.$user->getUsername()).'" onclick="return confirm(\''.T_('Are you sure?').'\');">'.T_('Delete').'</a>';
	    echo '</div>';
	}

	echo '</li>'."\n";
}
?>
<h3><?php echo T_('Check consistancy')?></h3>
<a href="<?php echo createURL('admin','checkUrl/') ?>"> <?php echo T_('Check all urls')  ?></a>

<?php
$this->includeTemplate('sidebar.tpl');
$this->includeTemplate($GLOBALS['bottom_include']);

?>
