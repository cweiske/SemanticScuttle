<?php


/* Managing all possible inputs */
$select_watchlist = isset($select_watchlist) ? $select_watchlist : '';
$select_all = isset($select_all) ? $select_all : '';

$selected = ' selected="selected"';
if (!isset($range)) {
    $range = '';
}
?>


<form id="search" action="<?php echo createURL('search'); ?>" method="post">
    <table>
    <tr>
        <?php
        $currentUser = $currentUsername = null;
        if ($userservice->isLoggedOn()) {
            $currentUser = $userservice->getCurrentObjectUser();
            $currentUsername = $currentUser->getUsername();
        }
        if ($userservice->isLoggedOn() || isset($user)) {
        ?>
        
        <td><input type="text" name="terms" size="30" value="<?php $terms=!isset($terms)?T_('Search...'):$terms; echo filter($terms); ?>" onfocus="if (this.value == '<?php echo T_('Search...') ?>') this.value = '';" onblur="if (this.value == '') this.value = '<?php echo T_('Search...') ?>';"/></td>
        <td><?php echo T_('in') ?></td>
        <td>
            <select name="range">                      
                <?php
                if ($range == 'user' && $user!=$currentUsername) {
                ?>
                <option value="<?php echo $user ?>"><?php echo T_("this user's bookmarks"); ?></option>
                <?php
                }
                if ($userservice->isLoggedOn()) {
                ?>
                <option value="<?php echo $currentUsername; ?>"><?php echo T_('my bookmarks'); ?></option>
                <option value="watchlist" <?php echo ($range == 'watchlist')?$selected:''?> ><?php echo T_('my watchlist'); ?></option>
                <?php
                }
                ?>
                <option value="all" <?php echo ($range == 'all' || $range == '')?$selected:'' ?> ><?php echo T_('all bookmarks'); ?></option>
            </select>
        </td>        
        <?php
        } else {
        ?>
        <td><input type="hidden" name="range" value="all" /></td>
        <?php
        }
        ?>        
        <td><input type="submit" value="<?php echo T_('Search' /* Submit button */); ?>" /></td>
    </tr>
    </table>
</form>
