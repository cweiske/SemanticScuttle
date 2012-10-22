<?php
$this->includeTemplate($GLOBALS['top_include']);
?>

<script type="text/javascript">
window.onload = function() {
    document.getElementById("username").focus();
}
</script>

<p><?php echo sprintf(T_('Sign up here to create a free %s account. All the information requested below is required'), $GLOBALS['sitename']); ?>.</p>

<form action="<?php echo $formaction; ?>" method="post">
<table>
<tr>
    <th align="left"><label for="username"><?php echo T_('Username'); ?></label></th>
    <td><input type="text" id="username" name="username" size="20" class="required" onkeyup="isAvailable(this, '')" /> </td>
    <td id="availability"><?php echo '←'.T_(' at least 5 characters, alphanumeric (no spaces, no dots or other special ones)') ?></td>
</tr>
<tr>
    <th align="left"><label for="password"><?php echo T_('Password'); ?></label></th>
    <td><input type="password" id="password" name="password" size="20" class="required" /></td>
    <td></td>
</tr>
<tr>
    <th align="left"><label for="password2"><?php echo T_('Repeat Password'); ?></label></th>
    <td><input type="password" id="password2" name="password2" size="20" class="required" /></td>
    <td></td>
</tr>
<tr>
    <th align="left"><label for="email"><?php echo T_('E-mail'); ?></label></th>
    <td><input type="text" id="email" name="email" size="40" class="required" value="<?php echo htmlspecialchars(POST_MAIL); ?>" /></td>
    <td><?php echo '←'.T_(' to send you your password if you forget it')?></td>
</tr>

<?php if(strlen($antispamQuestion)>0) {?>
<tr>
    <th align="left"><label for="antispamAnswer"><?php echo T_('Antispam question'); ?></label></th>
    <td><input type="text" id="antispamAnswer" name="antispamAnswer" size="40" class="required" value="<?php echo $antispamQuestion; ?>" onfocus="if (this.value == '<?php echo $antispamQuestion; ?>') this.value = '';" onblur="if (this.value == '') this.value = '<?php echo $antispamQuestion; ?>';"/></td>
    <td></td>
</tr>
<?php } ?>

<tr>
    <td></td>
    <td><input type="submit" name="submitted" value="<?php echo T_('Register'); ?>" /></td>
    <td></td>
</tr>
</table>
</form>

<?php
$this->includeTemplate($GLOBALS['bottom_include']);
?>
