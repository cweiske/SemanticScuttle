<?php
$this->includeTemplate($GLOBALS['top_include']);
?>

<script type="text/javascript">
window.onload = function() {
    document.getElementById("username").focus();
}
</script>

<p><?php echo sprintf(T_('Sign up here to create a free %s account. All the information requested below is required'), $GLOBALS['sitename']); ?>.</p>

<form<?php echo $form['attributes']; ?>>
<?php echo implode('', $form['hidden']); ?>
<table>
<tr>
    <th align="left"><?php echo $form['username']['labelhtml']; ?></th>
    <td><?php echo $form['username']['html']; ?></td>
    <td id="availability"><?php echo '←'.T_(' at least 5 characters, alphanumeric (no spaces, no dots or other special ones)') ?></td>
</tr>
<tr>
    <th align="left"><?php echo $form['password']['labelhtml']; ?></th>
    <td><?php echo $form['password']['html']; ?></td>
    <td></td>
</tr>
<tr>
    <th align="left"><?php echo $form['email']['labelhtml']; ?></th>
    <td><?php echo $form['email']['html']; ?></td>
    <td><?php echo '←'.T_(' to send you your password if you forget it')?></td>
</tr>

<?php if (isset($form['captcha'])) {?>
<tr>
    <th align="left"><?php echo $form['captcha']['labelhtml']; ?></th>
    <td><?php echo $form['captcha']['html']; ?></td>
    <td></td>
</tr>
<?php } ?>

<tr>
    <td></td>
    <td><?php echo $form['submit']['html']; ?></td>
    <td></td>
</tr>
</table>
</form>

<?php
$this->includeTemplate($GLOBALS['bottom_include']);
?>
