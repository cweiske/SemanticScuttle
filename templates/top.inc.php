<?php header('Content-Type: text/html; charset=utf-8'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title><?php echo filter($GLOBALS['sitename'] . (isset($pagetitle) ? ': ' . $pagetitle : '')); ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="icon" type="image/png"
	href="<?php echo ROOT ?>icon.png" />
<link rel="stylesheet" type="text/css"
	href="<?php echo ROOT ?>scuttle.css" />
<?php
if(isset($rsschannels)) {
	$size = count($rsschannels);
	for ($i = 0; $i < $size; $i++) {
		echo '<link rel="alternate" type="application/rss+xml" title="'. $rsschannels[$i][0] .'" href="'. $rsschannels[$i][1] .'" />';
	}
}
?>

<?php if (isset($loadjs)) :?>

<script type="text/javascript"
	src="<?php echo ROOT ?>jsScuttle.php"></script>


<link rel="stylesheet" type="text/css"
	href="http://ajax.googleapis.com/ajax/libs/dojo/1.2/dijit/themes/nihilo/nihilo.css">

<script type="text/javascript"
	src="http://ajax.googleapis.com/ajax/libs/dojo/1.2/dojo/dojo.xd.js"
	djConfig="parseOnLoad:true, isDebug:false, usePlainJson:true"></script>
 
<script type="text/javascript">
dojo.require("dojo.parser");
dojo.require("dojo.data.ItemFileReadStore");
dojo.require("dojox.form.MultiComboBox");
dojo.require("dijit.Tree");        
</script>
<?php endif ?>

</head>

<body class="nihilo">
<!-- the class is used by Dojo widgets -->

<?php
$headerstyle = '';
if(isset($_GET['popup'])) {
	$headerstyle = ' class="popup"';
}
?>

<div id="header" <?php echo $headerstyle; ?>>
<h1><a href="<?php echo ROOT ?>"><?php echo $GLOBALS['sitename']; ?></a></h1>
<?php
if(!isset($_GET['popup'])) {
	$this->includeTemplate('toolbar.inc');
}
?> <?php if(!isset($_GET['popup'])):?> <!--span id="welcome"><?php echo $GLOBALS['welcomeMessage'];?></span-->
<?php endif; ?></div>

<?php
if (isset($subtitle)) {
	echo '<h2>'. $subtitle ."</h2>\n";
}
if (isset($error) && $msg!='') {
	echo '<p class="error">'. $error ."</p>\n";
}
if (isset($msg) && $msg!='') {
	echo '<p class="success">'. $msg ."</p>\n";
}
?>