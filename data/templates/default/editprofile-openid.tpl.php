<?php
/**
 * User's own profile page: OpenID management
 *
 * @param array  $openIds        Array of OpenID association objects
 * @param string $formaction     URL where to send the forms to
 * @param SemanticScuttle_Model_User_OpenId
 *               $currentOpenId  Current OpenID object (may be null)
 */
?>
<h3><?php echo T_('OpenIDs'); ?></h3>
<?php if (count($openIds)) { ?>
<table>
 <thead>
  <tr>
   <th>Options</th>
   <th><?php echo T_('OpenID URL'); ?></th>
  </tr>
 </thead>
 <tbody>
 <?php foreach($openIds as $openId) { ?>
  <tr <?php if ($openId->isCurrent()) { echo 'class="openid-current"'; } ?>>
   <td>
    <form method="post" action="<?php echo $formaction; ?>">
     <input type="hidden" name="openIdUrl" value="<?php echo htmlspecialchars($openId->url); ?>"/>
     <button type="submit" name="action" value="deleteOpenId">
      <?php echo T_('delete'); ?>
     </button>
    </form>
   </td>
   <td><?php echo htmlspecialchars($openId->url); ?></td>
  </tr>
 <?php } ?>
 </tbody>
</table>
<?php } else { ?>
 <p><?php echo T_('No OpenIDs registered'); ?></p>
<?php } ?>

 <p>
  <form method="post" action="<?php echo $formaction; ?>">
   <label for="openid"><?php echo T_('New E-Mail or OpenID'); ?></label>
   <input type="text" id="openid" name="openid_identifier" size="20" />
   <button type="submit" name="action" value="registerOpenId">
    <?php echo T_('Register'); ?>
   </button>
  </form>
 </p>
