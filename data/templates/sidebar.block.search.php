<?php
/**
 * Show a list of the last searches.
 *
 * SemanticScuttle - your social bookmark manager.
 *
 * PHP version 5.
 *
 * @category    Bookmarking
 * @package     SemanticScuttle
 * @subcategory Templates
 * @author      Benjamin Huynh-Kim-Bang <mensonge@users.sourceforge.net>
 * @author      Christian Weiske <cweiske@cweiske.de>
 * @author      Eric Dane <ericdane@users.sourceforge.net>
 * @license     GPL http://www.gnu.org/licenses/gpl.html
 * @link        http://sourceforge.net/projects/semanticscuttle
 */
/* Service creation: only useful services are created */
$searchhistoryservice = SemanticScuttle_Service_Factory::get('SearchHistory');

$lastSearches = $searchhistoryservice->getAllSearches(
    'all', NULL, 3, NULL, true, false
);

if ($lastSearches && count($lastSearches) > 0) {
?>

<h2><?php echo T_('Last Searches'); ?></h2>
<div id="searches">
 <table>
<?php
foreach ($lastSearches as $row) {
    echo '  <tr><td>';
    echo  '<a href="'
        . htmlspecialchars(createURL('search', $range.'/'.$row['shTerms']))
        . '">';
    echo htmlspecialchars($row['shTerms']);
    echo '</a>';
    echo ' <span title="'
        . T_('Number of bookmarks for this query')
        . '">(' . $row['shNbResults'] . ')</span>';
    echo '</td></tr>' . "\n";
}
//echo '<tr><td><a href="'.createURL('users').'">...</a></td></tr>';
?>

 </table>
</div>
<?php
}
?>
