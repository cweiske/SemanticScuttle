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

if ($GLOBALS['enableGoogleCustomSearch'] == false) {
    echo "Google Custom Search disabled. You can enable it into the config.php file.";
    die;
}
?>

<!--?xml version="1.0" encoding="UTF-8" ?-->
<GoogleCustomizations>
    <CustomSearchEngine>
        <Title><?php echo $GLOBALS['sitename'] ?></Title>
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
