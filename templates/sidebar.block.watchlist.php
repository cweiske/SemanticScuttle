<?php
/* Service creation: only useful services are created */
//No specific services

$watching = $userservice->getWatchNames($userid);
?>

<h2><?php echo T_('Watching'); ?></h2>
<div id="watching">
    <ul>
    <?php foreach($watching as $watchuser): ?>
        <li><a href="<?php echo createURL('bookmarks', $watchuser); ?>"><?php echo $watchuser; ?></a> &rarr;</li>
    <?php endforeach; ?>
        <li>
          <form action="<?php echo createURL('watch', '');?>" method="post">
            <input type="text" name="contact" value="<?php echo T_('Add a contact...');?>" onfocus="if (this.value == '<?php echo T_('Add a contact...');?>') this.value = '';" onblur="if (this.value == '') this.value = '<?php echo T_('Add a contact...');?>';" title="<?php echo T_('Type a username to add it to your contacts.') ?>" />
          </form>
        </li>
    </ul>

</div>
