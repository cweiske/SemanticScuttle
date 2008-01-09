<?php
$this->includeTemplate($GLOBALS['top_include']);
?>

<form action="<?= $formaction ?>" method="post">
<input type="hidden" name="tag1" value="<?php echo $tag1 ?>" />
<input type="hidden" name="tag2" value="<?php echo $tag2 ?>" />
<p><?php echo T_('Are you sure?'); ?></p>
<p>
    <input type="submit" name="confirm" value="<?php echo T_('Yes'); ?>" />
    <input type="submit" name="cancel" value="<?php echo T_('No'); ?>" />
</p>

<?php if (isset($referrer)): ?>
<div><input type="hidden" name="referrer" value="<?php echo $referrer; ?>" /></div>
<?php endif; ?>

</form>

<?php
$this->includeTemplate($GLOBALS['bottom_include']); 
?>
