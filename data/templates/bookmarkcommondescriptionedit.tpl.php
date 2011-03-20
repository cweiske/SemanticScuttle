<?php

$this->includeTemplate($GLOBALS['top_include']);

list ($url, $hash) = explode('/', $_SERVER['PATH_INFO']);


?>
<script type="text/javascript">
window.onload = function() {
    document.getElementById("title").focus();
}
</script>

<form action="<?php echo $formaction; ?>" method="post">
<table title="<?php echo T_('Collaborative description: these fields can be viewed and modified by every users') ?>">
<tr>
    <th align="left"><?php echo T_('Title'); ?></th>
    <td><input type="text" id="title" name="title" size="75" maxlength="255" value="<?php echo $description['cdTitle']; ?>" onkeypress="this.style.backgroundImage = 'none';" /></td>
    <td></td>
</tr>
<tr>
    <th align="left"><?php echo T_('Description'); ?></th>
    <td><textarea name="description" cols="75" rows="10"><?php echo $description['cdDescription']; ?></textarea></td>
</tr>
<tr>
    <td></td>
    <td>
 	<?php
	if(strlen($description['cdDatetime'])>0) {
	   echo T_('Last modification:').' '.$description['cdDatetime'].', ';
	   $lastUser = $userservice->getUser($description['uId']);
	   echo  '<a href="'.createURL('profile', $lastUser['username']).'">'
           . SemanticScuttle_Model_UserArray::getName($lastUser) . '</a>';
	}
	?>
    </td>
    <td></td>
</tr>
<tr>
    <td></td>
    <td>
    <input type="submit" name="confirm" value="<?php echo T_('Update'); ?>" />
    <input type="submit" name="cancel" value="<?php echo T_('Cancel'); ?>" />
    </td>
    <td></td>
</tr>
</table>

<?php if (isset($referrer)): ?>
<div><input type="hidden" name="referrer" value="<?php echo $referrer; ?>" /></div>
<?php endif; ?>
<div><input type="hidden" name="hash" value="<?php echo $hash; ?>" /></div>
</form>

<?php
$this->includeTemplate($GLOBALS['bottom_include']); 
?>
