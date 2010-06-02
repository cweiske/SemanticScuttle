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

<form<?php echo $form['attributes']; ?>>
<?php echo implode('', $form['hidden']); ?>
  <table>
    <tr>
      <th align="left"><?php echo $form['username']['labelhtml']; ?></th>
      <td><?php echo $form['username']['html']; ?></td>
      <td></td>
    </tr>
    <tr>
      <th align="left"><?php echo $form['password']['labelhtml']; ?></th>
      <td><?php echo $form['password']['html']; ?></td>
      <td><?php echo $form['keeploggedin']['html']
                   . $form['keeploggedin']['labelhtml']; ?></td>
    </tr>
    <tr>
      <td></td>
      <td><?php echo $form['submit']['html']; ?></td>
      <td></td>
    </tr>
    </table>
    <p>&raquo; <a href="<?php echo ROOT ?>password.php"><?php echo T_('Forgotten your password?') ?></a></p>
</form>

<?php
$this->includeTemplate($GLOBALS['bottom_include']);
?>