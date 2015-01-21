<?php echo '<'; ?>?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
 <head>
  <meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />
  <title><?php echo filter($GLOBALS['sitename'] .(isset($pagetitle) ? ' » ' . $pagetitle : '')); ?></title>
  <link rel="icon" type="image/png" href="<?php echo $theme->resource('icon.png');?>" />
  <link rel="stylesheet" type="text/css" href="<?php echo $theme->resource('scuttle.css');?>" />
  <link rel="search" type="application/opensearchdescription+xml" href="<?php echo ROOT ?>api/opensearch.php" title="<?php echo htmlspecialchars($GLOBALS['sitename']) ?>"/>
<?php
if (isset($rsschannels)) {
	$size = count($rsschannels);
	for ($i = 0; $i < $size; $i++) {
		echo '  <link rel="alternate" type="application/rss+xml" title="'
            . htmlspecialchars($rsschannels[$i][0]) . '"'
            . ' href="'. htmlspecialchars($rsschannels[$i][1]) .'" />' . "\n";
	}
}
?>

<?php if (isset($loadjs)) :?>
<?php if (DEBUG_MODE) : ?>
  <script type="text/javascript" src="<?php echo ROOT_JS ?>jquery-1.4.2.js"></script>
  <script type="text/javascript" src="<?php echo ROOT_JS ?>jquery.jstree.js"></script>
<?php else: ?>
  <script type="text/javascript" src="<?php echo ROOT_JS ?>jquery-1.4.2.min.js"></script>
  <script type="text/javascript" src="<?php echo ROOT_JS ?>jquery.jstree.min.js"></script>
<?php endif ?>
  <script type="text/javascript" src="<?php echo ROOT ?>jsScuttle.php"></script>
<?php endif ?>

 </head>
<?php
$bodystyle = '';
if (isset($_GET['popup'])) {
    if (isset($_GET['height'])) {
        $bodystyle .= 'height:' . intval($_GET['height']) . 'px;';
    }
    if (isset($_GET['width'])) {
        $bodystyle .= 'width:' . intval($_GET['width']) . 'px;';
    }
    if ($bodystyle != '') {
        $bodystyle = ' style="' . $bodystyle . '"';
    }
}
?>
 <body<?php echo $bodystyle; ?>>

<?php
$headerstyle = '';
if (isset($_GET['popup'])) {
	$headerstyle = ' class="popup"';
}
?>

<div id="header" <?php echo $headerstyle; ?>>
<h1><a href="<?php echo ROOT ?>"><?php echo $GLOBALS['sitename']; ?></a></h1>
<?php
if(!isset($_GET['popup'])) {
	$this->includeTemplate('toolbar.inc');
}
?></div>

<?php
if (isset($subtitlehtml)) {
	echo '<h2>' . $subtitlehtml . "</h2>\n";
} else if (isset($subtitle)) {
      echo '<h2>' . htmlspecialchars($subtitle) . "</h2>\n";
}
if(DEBUG_MODE) {
	echo '<p class="error">'. T_('Admins, your installation is in "Debug Mode" ($debugMode = true). To go in "Normal Mode" and hide debugging messages, change $debugMode to false into config.php.') ."</p>\n";
}
if (isset($error) && $error!='') {
	echo '<p class="error">'. $error ."</p>\n";
}
if (isset($msg) && $msg!='') {
	echo '<p class="success">'. $msg ."</p>\n";
}
if (isset($tipMsg) && $tipMsg!='') {
	echo '<p class="tipMsg">'. $tipMsg ."</p>\n";
}
?>
