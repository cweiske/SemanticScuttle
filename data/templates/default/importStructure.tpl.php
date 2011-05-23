<?php
$this->includeTemplate($GLOBALS['top_include']);
?>

<div id="bookmarks">
<form id="import" enctype="multipart/form-data"
	action="<?php echo $formaction; ?>" method="post">
<table>
	<tr valign="top">
		<th align="left"><?php echo T_('File'); ?></th>
		<td><input type="hidden" name="MAX_FILE_SIZE" value="1024000" /> <input
			type="file" name="userfile" size="50" /></td>
	</tr>
	<tr>
		<td />
		<td><input type="submit" value="<?php echo T_('Import'); ?>" /></td>
	</tr>
</table>
</form>

<h3><?php echo T_('Instructions'); ?></h3>
<ol>
	<li>
	<p><?php echo T_('Create your structure into a simple text file and following this model:');?></p>
	<ul>
		<li>firstTagOfLevel1</li>
		<li>&nbsp;&nbsp;&nbsp;&nbsp;firstTagOfLevel2 <i>(the line starts with two spaces)</i></li>
		<li>&nbsp;&nbsp;&nbsp;&nbsp;secondTagOfLevel2</li>
		<li>&nbsp;&nbsp;&nbsp;&nbsp;thirdTagOfLevel2</li>
		<li>secondTagOfLevel1</li>
		<li>&nbsp;&nbsp;&nbsp;&nbsp;fourthTagOfLevel2 <i>(included into secondTagOfLevel1)</i></li>
	</ul>
	</li>
	<li>
	<p><?php echo T_('Then import the file. The tags and their relations will be added to your profile.'); ?></p>
	</li>
</ol>
</div>

<?php
$this->includeTemplate($GLOBALS['bottom_include']);
?>