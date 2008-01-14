<?php
$this->includeTemplate($GLOBALS['top_include']);
?>

<form action="<?= $formaction ?>" method="post">
<input type="hidden" name="tag" value="<?php echo $tag ?>" />
<p><?php echo T_('Create new link:')?></p>
<p><?php echo $tag ?> > <input type="text" name="newTag" /></p>
<!--p><?php echo T_('Are you sure?'); ?></p-->
<p>
    <input type="submit" name="confirm" value="<?php echo T_('Create'); ?>" />
    <input type="submit" name="cancel" value="<?php echo T_('Cancel'); ?>" />
</p>

<?php if (isset($referrer)): ?>
<div><input type="hidden" name="referrer" value="<?php echo $referrer; ?>" /></div>
<?php endif; ?>

</form>

<?php
$this->includeTemplate($GLOBALS['bottom_include']); 
?>
