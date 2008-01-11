<?php
$tagservice =& ServiceFactory::getServiceInstance('TagService');
$userservice =& ServiceFactory::getServiceInstance('UserService');

$logged_on_userid = $userservice->getCurrentUserId();
if ($logged_on_userid === false) {
    $logged_on_userid = NULL;
}
$lastUsers = $userservice->getUsers(3);

if ($lastUsers && count($lastUsers) > 0) {
?>

<h2><?php echo T_('Last Users'); ?></h2>
<div id="users">
<table>
<?php
foreach ($lastUsers as $row) {
    echo '<tr><td>';
    echo  '<a href="'.createURL('profile', $row['username']).'">';
    echo $row['username'];
    echo '</a>';
    echo '</td></tr>';
}
echo '<tr><td><a href="'.createURL('users').'">...</a></td></tr>';
?>

</table>
</div>
<?php
}
?>
