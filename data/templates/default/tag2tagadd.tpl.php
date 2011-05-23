<?php
$this->includeTemplate($GLOBALS['top_include']);
?>

<form action="<?php echo $formaction; ?>" method="post">

<p align="right" style="float:right">
<small style="text-align:right"><?php echo T_('Note: use "=" to make synonym two tags. e.g.: france=frenchcountry')?></small><br/>
<small style="text-align:right"><?php echo T_('Note: use ">" to include one tag in another. e.g.: europe>france>paris')?></small><br/>
</p>

<p><?php echo T_('Create new link:')?></p>
<p>
<input type="text" name="tag1" value="<?php echo $tag1 ?>"/>
<input type="text" name="linkType" value=">" size="1" maxlength="1"/>
<input type="text" name="tag2" />
</p>
<p>
<small style="text-align:right"><?php echo sprintf(T_('Note: include a tag into \'%s\' tag (e.g. %s>countries) display the tag into the menu box'), $GLOBALS['menuTag'], $GLOBALS['menuTag'])?></small>
</p>
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
if(count($links)>0) {
echo T_("Existing links:");
foreach($links as $link) {
    echo '<span style="white-space:nowrap;margin-left:25px;">';
    if($link['tag1'] == $tag1 || $link['tag1'] == $tag2) {
	$textTag1 = '<b>'.$tag1.'</b>';
    } else {
	$textTag1 = $link['tag1'];
    }
    if($link['tag2'] == $tag1 || $link['tag2'] == $tag2) {
	$textTag2 = '<b>'.$tag2.'</b>';
    } else {
	$textTag2 = $link['tag2'];
    }

    echo $textTag1.' '.$link['relationType'].' '.$textTag2;
    echo "</span> ";
}
} else {
    echo T_('No links');
}

$this->includeTemplate($GLOBALS['bottom_include']); 
?>
