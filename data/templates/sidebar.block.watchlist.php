<?php
/* Service creation: only useful services are created */
//No specific services

$watching = $userservice->getWatchNames($userid);
$watchedBy = $userservice->getWatchNames($userid, true);


$closeContacts = array(); // people in my contacts list and who I am also in the contacts list
foreach($watching as $watchuser) {
	if(in_array($watchuser, $watchedBy)) {
		$closeContacts[] = $watchuser;
	}
}

?>

<?php if(count($closeContacts)>0):?>
<h2 title="<?php echo T_('Close contacts are mutual contacts');?>"><?php echo ' ↔ '. T_('Close contacts'); ?></h2>
<div id="watching">
    <ul>
    <?php foreach($closeContacts as $watchuser): ?>
        <li><a href="<?php echo createURL('bookmarks', $watchuser); ?>"><?php echo $watchuser; ?></a> </li>
    <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>


<h2><?php echo ' → '. T_('Watching'); ?></h2>
<div id="watching">
    <ul>
        <?php if($userservice->isLoggedOn() && $currentUser->getUsername() == $user): ?>            
        <li>
          <form action="<?php echo createURL('watch', '');?>" method="post">
            <input type="text" name="contact" value="<?php echo T_('Add a contact...');?>" onfocus="if (this.value == '<?php echo T_('Add a contact...');?>') this.value = '';" onblur="if (this.value == '') this.value = '<?php echo T_('Add a contact...');?>';" title="<?php echo T_('Type a username to add it to your contacts.') ?>" />
          </form>
        </li>
        <?php endif; ?>
    
    <?php foreach($watching as $watchuser): ?>
        <li><a href="<?php echo createURL('bookmarks', $watchuser); ?>"><?php echo $watchuser; ?></a>
        <?php if($userservice->isLoggedOn() && $currentUser->getUsername() == $user): ?>
         - <a href="<?php echo createUrl('watch','?contact='.$watchuser); ?>" title="<?php echo T_('Remove this contact'); ?>">x</a>
        </li>
        <?php endif; ?>  
    <?php endforeach; ?>
        
    </ul>
</div>

<h2><?php echo ' ← '. T_('Watched By'); ?></h2>
<div id="watching">
    <ul>
    <?php foreach($watchedBy as $watchuser): ?>
        <li><a href="<?php echo createURL('bookmarks', $watchuser); ?>"><?php echo $watchuser; ?></a> </li>
    <?php endforeach; ?>
    </ul>

</div>
