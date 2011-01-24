<?php
/**
 * Google custom search
 *
 * PHP version 5.
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Benjamin Huynh-Kim-Bang <mensonge@users.sourceforge.net>
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @author   Eric Dane <ericdane@users.sourceforge.net>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */

require_once '../www-header.php';

if ($GLOBALS['enableGoogleCustomSearch']==false) {
    echo "Google Custom Search disabled. You can enable it into the config.php file.";
    die;
}

?>

<html>
<title><?php echo $GLOBALS['sitename'] ?></title>
<body>
<center>
<br />

<!-- Google CSE Search Box Begins  -->
<form id="cref" action="http://www.google.com/cse">
  <input type="hidden" name="cref" value="<?php echo ROOT;?>gsearch/context.php" />
  <input type="text" name="q" size="40" />
  <input type="submit" name="sa" value="Search" />
</form>
<script type="text/javascript" src="http://www.google.com/coop/cse/brand?form=cref"></script>

<!-- Google CSE Search Box Ends -->
<small>Based on <a href="http://www.google.com/coop/cse/">Google Custom Search</a> over this <a href="<?php echo ROOT ?>api/export_gcs.php">list of websites</a> from <?php echo $GLOBALS['sitename'] ?>.</small>
<br />
<br />
<small><a href="<?php echo ROOT?>"><?php echo T_('Come back to ').$GLOBALS['sitename'] ?>...</a></small>

<?php
if ($userservice->isLoggedOn() && $currentUser->isAdmin()) {
    echo '<p><small>';
    echo T_('Admin tips: ');
    echo T_('To refresh manually Google Custom Search Engine, goes to: ');
    echo '<a href="http://www.google.com/coop/cse/cref?cref='.ROOT.'search/context.php">http://www.google.com/coop/cse/cref</a><br/>';
    echo T_('If no result appears, check that all the urls are valid in the admin section.');
    echo '</small></p>';
}
?>

</center>
</body>
</html>
