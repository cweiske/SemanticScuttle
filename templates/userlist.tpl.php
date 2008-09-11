<?php

$userservice =& ServiceFactory::getServiceInstance('UserService');

$currentUser = $userservice->getCurrentUser();
$currentUserID = $userservice->getCurrentUserId();
$currentUsername = $currentUser[$userservice->getFieldName('username')];


$this->includeTemplate($GLOBALS['top_include']);

echo '<ol id="bookmarks">';

foreach(array_keys($users) as $key) {

	echo '<li class="xfolkentry">'."\n";

	echo '<div class="link">';
	echo '<a href="'.createURL('profile', $users[$key][$userservice->getFieldname('username')]).'">'.$users[$key][$userservice->getFieldName('username')].'</a>';
	echo '</div>';

	if($users[$key][$userservice->getFieldName('username')] != $currentUsername) {
	    echo '<div class="meta">';
	    echo '<a href="'.createURL('admin','delete/'.$users[$key][$userservice->getFieldname('username')]).'" onclick="return confirm(\''.T_('Are you sure?').'\');">'.T_('Delete').'</a>';
	    echo '</div>';
	}

	echo '</li>'."\n";
}

$this->includeTemplate('sidebar.tpl');
$this->includeTemplate($GLOBALS['bottom_include']);

?>
