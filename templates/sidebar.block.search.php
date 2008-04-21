<?php
$userservice =& ServiceFactory::getServiceInstance('UserService');
$searchhistoryservice =& ServiceFactory::getServiceInstance('SearchHistoryService');

$logged_on_userid = $userservice->getCurrentUserId();
if ($logged_on_userid === false) {
    $logged_on_userid = NULL;
}

$lastSearches = $searchhistoryservice->getAllSearches('all', NULL, 3, NULL, true);

if ($lastSearches && count($lastSearches) > 0) {
?>

<h2><?php echo T_('Last Searches'); ?></h2>
<div id="searches">
<table>
<?php
foreach ($lastSearches as $row) {
    echo '<tr><td>';
    echo  '<a href="'.createURL('search', $range.'/'.$row['shTerms']).'">';
    echo $row['shTerms'];
    echo '</a>';
    echo ' <span title="'.T_('Number of bookmarks for this query').'">('.$row['shNbResults'].')</span>';
    echo '</td></tr>';
}
//echo '<tr><td><a href="'.createURL('users').'">...</a></td></tr>';
?>

</table>
</div>
<?php
}
?>
