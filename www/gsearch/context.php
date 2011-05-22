<?php
/**
 * Google custom search context information for SemanticScuttle.
 * Tells Google meta data about the search.
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
<?xml version="1.0" encoding="UTF-8" ?>
<GoogleCustomizations>
 <CustomSearchEngine>
  <Title><?php echo htmlspecialchars($GLOBALS['sitename']) ?></Title>
  <Description><?php echo filter($GLOBALS['welcomeMessage']) ?></Description>
  <Context>
   <BackgroundLabels>
    <Label name="include" mode="FILTER" />
   </BackgroundLabels>
  </Context>
  <LookAndFeel nonprofit="false">
  </LookAndFeel>
 </CustomSearchEngine>
 <Include type="Annotations" href="<?php echo ROOT;?>api/export_gcs.php?xml=1" />
</GoogleCustomizations>
