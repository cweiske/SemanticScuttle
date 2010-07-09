<?php
$this->includeTemplate($GLOBALS['top_include']);
?>

<p><?php echo sprintf(T_('If you have forgotten your password, %s can generate a new one. Enter the username and e-mail address of your account into the form below and we will e-mail your new password to you.'), $GLOBALS['sitename']); ?></p>

<form<?php echo $form['attributes']; ?>>
<?php echo implode('', $form['hidden']); ?>
 <table>
  <tr>
   <th align="left"><?php echo $form['username']['labelhtml']; ?></th>
   <td><?php echo $form['username']['html']; ?></td>
  </tr>
  <tr>
   <th align="left"><?php echo $form['email']['labelhtml']; ?></th>
   <td><?php echo $form['email']['html']; ?></td>
  </tr>
  <tr>
   <th align="left"><?php echo $form['captcha']['labelhtml']; ?></th>
   <td><?php echo $form['captcha']['html']; ?></td>
  </tr>
  <tr>
   <td></td>
   <td><?php echo $form['submit']['html']; ?></td>
  </tr>
 </table>
</form>

<?php
$this->includeTemplate($GLOBALS['bottom_include']);
?>