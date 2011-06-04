<?php
$this->includeTemplate($GLOBALS['top_include']);
?>

<script type="text/javascript">
window.onload = function() {
    document.getElementById("username").focus();
}
</script>

<?php
if (!$userservice->isSessionStable()) {
    echo '<p class="error">'.T_('Please activate cookies').'</p>';
}
?>

<form action="<?php echo $formaction; ?>" method="post">
    <div><input type="hidden" name="query" value="<?php echo $querystring; ?>" /></div>
    <table>
    <tr>
        <th align="left"><label for="username"><?php echo T_('Username'); ?></label></th>
        <td><input type="text" id="username" name="username" size="20" /></td>
        <td></td>
    </tr>
    <tr>
        <th align="left"><label for="password"><?php echo T_('Password'); ?></label></th>
        <td><input type="password" id="password" name="password" size="20" /></td>
        <td><input type="checkbox" name="keeppass" id="keeppass" value="yes" /> <label for="keeppass"><?php echo T_("Don't ask for my password for 2 weeks"); ?>.</label></td>
    </tr>
    <tr>
        <td></td>
        <td><input type="submit" name="submitted" value="<?php echo T_('Log In'); ?>" /></td>
        <td></td>
    </tr>
    </table>
    <p>&raquo; <a href="<?php echo ROOT ?>password.php"><?php echo T_('Forgotten your password?') ?></a></p>
</form>

<?php
$this->includeTemplate($GLOBALS['bottom_include']);
?>