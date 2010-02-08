<?php
require_once '../www-header.php';

if($GLOBALS['enableGoogleCustomSearch'] == false) {
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
