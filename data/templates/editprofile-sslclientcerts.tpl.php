<h3><?php echo T_('SSL client certificates'); ?></h3>
<?php if (count($sslClientCerts)) { ?>
<table>
 <thead>
  <tr>
   <th><?php echo T_('Serial'); ?></th>
   <th><?php echo T_('Name'); ?></th>
   <th><?php echo T_('Email'); ?></th>
   <th><?php echo T_('Issuer'); ?></th>
  </tr>
 </thead>
 <tbody>
 <?php foreach($sslClientCerts as $cert) { ?>
   <tr <?php if ($cert->isCurrent()) { echo 'class="ssl-current"'; } ?>>
   <td><?php echo htmlspecialchars($cert->sslSerial); ?></td>
   <td><?php echo htmlspecialchars($cert->sslName); ?></td>
   <td><?php echo htmlspecialchars($cert->sslEmail); ?></td>
   <td><?php echo htmlspecialchars($cert->sslClientIssuerDn); ?></td>
  </tr>
 <?php } ?>
 </tbody>
</table>
<?php } else { ?>
 <p><?php echo T_('No certificates registered'); ?></p>
<?php } ?>
