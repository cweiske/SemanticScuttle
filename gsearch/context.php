<?php require_once('../header.inc.php');?>

<!--?xml version="1.0" encoding="UTF-8" ?-->
<GoogleCustomizations>
    <CustomSearchEngine>
        <Title><?php echo $GLOBALS['sitename'] ?></Title>
        <Description><?php echo $GLOBALS['welcomeMessage'] ?></Description>
        <Context>
           <BackgroundLabels>
             <Label name="include" mode="FILTER" />
          </BackgroundLabels>
        </Context>
        <LookAndFeel nonprofit="false">
        </LookAndFeel>
    </CustomSearchEngine>

    <Include type="Annotations" href="<?php echo $GLOBALS['root'];?>api/export_gcs.php?xml=1" />


</GoogleCustomizations>
