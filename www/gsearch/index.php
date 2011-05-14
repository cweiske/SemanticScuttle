<?php
/**
 * Google custom search for SemanticScuttle.
 * This is a "linked CSE", a linked custom search engine.
 * See the linked docs for more info.
 *
 * SemanticScuttle - your social bookmark manager.
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
 * @link     http://www.google.com/cse/docs/cref.html
 */
require_once '../www-header.php';

if ($GLOBALS['enableGoogleCustomSearch'] == false) {
    header('HTTP/1.0 403 Forbidden');
    header('Content-Type: text/plain; charset=utf-8');
    echo "Google Custom Search disabled."
        . " You can enable it into the config.php file.\n";
    die();
}

?>
<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
 <head>
  <title><?php echo htmlspecialchars($GLOBALS['sitename']); ?></title>
 </head>
 <body>
  <div style="text-align: center; padding-top: 2em">

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


<?php if ($userservice->isLoggedOn() && $currentUser->isAdmin()) {
    echo '<p><small>';
    echo T_('Admin tips: ');
    echo T_('To refresh manually Google Custom Search Engine, goes to: ');
    echo '<a href="http://www.google.com/coop/cse/cref?cref='
        . ROOT . 'gsearch/context.php">http://www.google.com/coop/cse/cref</a>'
        . '<br/>';
    echo T_('If no result appears, check that all the urls are valid in the admin section.');
    echo '</small></p>';
    
}
?>

  </div>
 </body>
</html>


