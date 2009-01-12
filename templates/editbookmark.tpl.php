<?php
$this->includeTemplate($GLOBALS['top_include']);

$accessPublic = '';
$accessShared = '';
$accessPrivate = '';
switch ($row['bStatus']) {
    case 0 :
        $accessPublic = ' selected="selected"';
        break;
    case 1 :
        $accessShared = ' selected="selected"';
        break;
    case 2 :
        $accessPrivate = ' selected="selected"';
        break;
}

$this->includeTemplate("dojo.inc");
?>



<script type="text/javascript">
//window.onload = function() {
//    document.getElementById("address").focus();
//}
</script>

<form action="<?php echo $formaction; ?>" method="post">
<table>
<tr>
    <th align="left"><?php echo T_('Address'); ?></th>
    <td><input type="text" id="address" name="address" size="75" maxlength="65535" value="<?php echo filter($row['bAddress'], 'xml'); ?>" onblur="useAddress(this)" /></td>
    <td>&larr; <?php echo T_('Required'); ?></td>
</tr>
<tr>
    <th align="left"><?php echo T_('Title'); ?></th>
    <td><input type="text" id="titleField" name="title" size="75" maxlength="255" value="<?php echo filter($row['bTitle'], 'xml'); ?>" onkeypress="this.style.backgroundImage = 'none';" /></td>
    <td>&larr; <?php echo T_('Required'); ?></td>
</tr>
<tr>
    <th align="left"><?php echo T_('Description'); ?></th>
    <td><textarea name="description" rows="5" cols="63" ><?php echo filter($row['bDescription'], 'xml'); ?></textarea></td>
    <td>&larr; <?php echo T_('You can use anchors to delimite attributes. for example: [publisher]blah[/publisher] '); ?></td>
</tr>
<tr>
    <th align="left"><?php echo T_('Tags'); ?></th>
    <td class="scuttletheme">
    <span dojoType="dojo.data.ItemFileReadStore" jsId="memberTagStore" url="<?php echo ROOT?>ajax/gettags.php"></span>
    <input type="text" dojoType="js.MultiComboBox" id="tags" name="tags" size="75" value="<?php echo filter(implode(', ', $row['tags']), 'xml'); ?>" store="memberTagStore" delimiter="," searchAttr="tag" hasDownArrow="false"/></td>
    <td>&larr; <?php echo T_('Comma-separated'); ?></td>
</tr>
<tr>
    <th></th>
    <td align="right"><small><?php echo T_('Note: use ">" to include one tag in another. e.g.: europe>france>paris')?><small></td>
</tr>
<tr>
    <th></th>
    <td align="right"><small><?php echo T_('Note: use "=" to make synonym two tags. e.g.: france=frenchcountry')?><small></td>
</tr>
<tr>
    <th align="left"><?php echo T_('Privacy'); ?></th>
    <td>
        <select name="status">
            <option value="0"<?php echo $accessPublic ?>><?php echo T_('Public'); ?></option>
            <option value="1"<?php echo $accessShared ?>><?php echo T_('Shared with Watch List'); ?></option>
            <option value="2"<?php echo $accessPrivate ?>><?php echo T_('Private'); ?></option>
        </select>
    </td>
    <td></td>
</tr>
<tr>
    <td></td>
    <td>
        <input type="submit" name="submitted" value="<?php echo $btnsubmit; ?>" />
        <?php
        if (isset($showdelete) && $showdelete) {
        ?>
        <input type="submit" name="delete" value="<?php echo T_('Delete Bookmark'); ?>" />
        <?php
        }
        if (isset($showdelete) && $showdelete) {
			echo ' (<a href="'.createURL('bookmarkcommondescriptionedit', $row['bHash']).'">';
			echo T_('edit common description').'</a>)';
        }
        
        if ($popup) {
        ?>
        <input type="hidden" name="popup" value="1" />
        <?php
        } elseif (isset($referrer)) {
        ?>
        <input type="hidden" name="referrer" value="<?php echo $referrer; ?>" />
        <?php
        }
        ?>
    </td>
    <td></td>
</tr>
</table>
</form>

<?php
// Dynamic tag selection
$this->includeTemplate('dynamictags.inc');

// Bookmarklets and import links
if (empty($_REQUEST['popup']) && (!isset($showdelete) || !$showdelete)) {
?>

<h3><?php echo T_('Bookmarklet'); ?></h3>
<p>
<script type="text/javascript">
var browser=navigator.appName;
if (browser == "Opera")
    {
    document.write("<?php echo sprintf(T_("Click one of the following bookmarklets to add a button you can click whenever you want to add the page you are on to %s"), $GLOBALS['sitename']); ?>:</p>");
    }
else
    {
    document.write("<?php echo sprintf(T_("Drag one of the following bookmarklets to your browser's bookmarks and click it whenever you want to add the page you are on to %s"), $GLOBALS['sitename']); ?>:</p>");
    }
var selection = '';
if (window.getSelection) {
    selection = 'window.getSelection()';
} else if (document.getSelection) {
    selection = 'document.getSelection()';
} else if (document.selection) {
    selection = 'document.selection.createRange().text';
}
document.write('<ul>');
if (browser == "Opera")
    {
    document.write('<li><a href="opera:/button/Go%20to%20page,%20%22javascript:x=document;a=encodeURIComponent(x.location.href);t=encodeURIComponent(x.title);d=encodeURIComponent('+selection+');location.href=\'<?php echo createURL('bookmarks', $GLOBALS['user']); ?>?action=add&amp;address=\'+a+\'&amp;title=\'+t+\'&amp;description=\'+d;void 0%22;,,%22Post%20to%20<?php echo $GLOBALS['sitename']; ?>%22,%22Scuttle%22"><?php echo sprintf(T_('Post to %s'), $GLOBALS['sitename']); ?><\/a><\/li>');	
    document.write('<li><a href="opera:/button/Go%20to%20page,%20%22javascript:x=document;a=encodeURIComponent(x.location.href);t=encodeURIComponent(x.title);d=encodeURIComponent('+selection+');open(\'<?php echo createURL('bookmarks', $GLOBALS['user']); ?>?action=add&amp;popup=1&amp;address=\'+a+\'&amp;title=\'+t+\'&amp;description=\'+d,\'<?php echo $GLOBALS['sitename']; ?>\',\'modal=1,status=0,scrollbars=1,toolbar=0,resizable=1,width=790,height=465,left=\'+(screen.width-790)/2+\',top=\'+(screen.height-425)/2);void 0;%22,,%22Post%20to%20<?php echo $GLOBALS['sitename']; ?>%20(Pop-up)%22,%22Scuttle%22"><?php echo sprintf(T_('Post to %s (Pop-up)'), $GLOBALS['sitename']); ?><\/a><\/li>');
    }
else
    {
    document.write('<li><a href="javascript:x=document;a=encodeURIComponent(x.location.href);t=encodeURIComponent(x.title);d=encodeURIComponent('+selection+');location.href=\'<?php echo createURL('bookmarks', $GLOBALS['user']); ?>?action=add&amp;address=\'+a+\'&amp;title=\'+t+\'&amp;description=\'+d;void 0;"><?php echo sprintf(T_('Post to %s'), $GLOBALS['sitename']); ?><\/a><\/li>');
    document.write('<li><a href="javascript:x=document;a=encodeURIComponent(x.location.href);t=encodeURIComponent(x.title);d=encodeURIComponent('+selection+');open(\'<?php echo createURL('bookmarks', $GLOBALS['user']); ?>?action=add&amp;popup=1&amp;address=\'+a+\'&amp;title=\'+t+\'&amp;description=\'+d,\'<?php echo $GLOBALS['sitename']; ?>\',\'modal=1,status=0,scrollbars=1,toolbar=0,resizable=1,width=790,height=465,left=\'+(screen.width-790)/2+\',top=\'+(screen.height-425)/2);void 0;"><?php echo sprintf(T_('Post to %s (Pop-up)'), $GLOBALS['sitename']); ?><\/a><\/li>');
    }
document.write('<\/ul>');
</script>

<h3><?php echo T_('Import'); ?></h3>
<ul>
    <li><a href="<?php echo createURL('importNetscape'); ?>"><?php echo T_('Import bookmarks from bookmark file'); ?></a> (<?php echo T_('Internet Explorer, Mozilla Firefox and Netscape'); ?>)</li>
    <li><a href="<?php echo createURL('import'); ?>"><?php echo T_('Import bookmarks from del.icio.us'); ?></a></li>
</ul>

<?php
}
$this->includeTemplate($GLOBALS['bottom_include']); 
?>
