<?php require_once('../header.inc.php');

if($GLOBALS['enableGoogleCustomSearch']==false) {
    echo "Google Custom Search disabled. You can enable it into the config.inc.php file.";
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
<small>Based on <a href="http://www.google.com/coop/cse/">Google Custom Search</a> over this <a href="../api/export_gcs.php">list of websites</a> from <?php echo $GLOBALS['sitename'] ?>.</small>


<!--
To refresh manually Google Custom Search Engine, goes to: http://www.google.com/coop/cse/cref
-->



</center>
</body>
</html>


