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

function jsEscTitle($title)
{
    return addcslashes($title, "'");
}

if (is_array($row['tags'])) {
    $row['tags'] = implode(', ', $row['tags']);
}

$ajaxUrl = ROOT . 'ajax/'
    . (
        ($GLOBALS['adminsAreAdvisedTagsFromOtherAdmins'] && $currentUser->isAdmin())
            ? 'getadmintags'
            : 'getcontacttags'
    ) . '.php';
?>
<form action="<?php echo $formaction; ?>" method="post">
<table>
<tr>
    <th align="left"><?php echo T_('Address'); ?></th>
    <td><input type="text" id="address" name="address" size="75" maxlength="65535" value="<?php echo filter($row['bAddress'], 'xml'); ?>" onblur="useAddress(this)" /></td>
    <td>← <?php echo T_('Required'); ?></td>
</tr>
<tr>
    <th align="left"><?php echo T_('Title'); ?></th>
    <td><input type="text" id="titleField" name="title" size="75" maxlength="255" value="<?php echo filter($row['bTitle'], 'xml'); ?>" onkeypress="this.style.backgroundImage = 'none';" /></td>
    <td>← <?php echo T_('Required'); ?></td>
</tr>
<tr>
    <th align="left">
    <?php echo T_('Description'); ?>
    <a onclick="var nz = document.getElementById('privateNoteZone'); nz.style.display='';this.style.display='none';"><?php echo T_("Add Note"); ?></a>
    </th>
    <td><textarea name="description" id="description" rows="5" cols="63" ><?php echo filter($row['bDescription'], 'xml'); ?></textarea></td>
    <td>← <?php echo T_('You can use anchors to delimite attributes. for example: [publisher]blah[/publisher] '); ?>
    <?php if(count($GLOBALS['descriptionAnchors'])>0): ?>
    <br /><br />
    <?php echo T_('Suggested anchors: '); ?>
	<?php foreach($GLOBALS['descriptionAnchors'] as $anchorName => $anchorValue): ?>
    <?php if(is_numeric($anchorName)) {
    	$anchorName = $anchorValue;
    	$anchorValue = '['.$anchorValue.']'.'[/'.$anchorValue.']';
    } ?>
    <span class="anchor" title="<?php echo $anchorValue ?>" onclick="addAnchor('<?php echo $anchorValue ?>', 'description')"><?php echo $anchorName ?></span>
    <?php endforeach; ?>
    <?php endif; ?>
    </td>
</tr>
<tr id="privateNoteZone" <?php if(strlen($row['bPrivateNote'])==0):?>style="display:none"<?php endif; ?>>
    <th align="left"><?php echo T_('Private Note'); ?></th>
    <td><textarea name="privateNote" id="privateNote" rows="1" cols="63" ><?php echo filter($row['bPrivateNote'], 'xml'); ?></textarea></td>
    <td>← <?php echo T_('Just visible by you and your contacts.'); ?>
    </td>
</tr>
<tr>
    <th align="left"><?php echo T_('Tags'); ?></th>
    <td class="scuttletheme">
     <input type="text" id="tags" name="tags" size="75" value="<?php echo filter($row['tags'], 'xml'); ?>"/>
    </td>
    <td>← <?php echo T_('Comma-separated'); ?></td>
</tr>
<tr>
    <th></th>
    <td align="right"><small><?php echo htmlspecialchars(T_('Note: use ">" to include one tag in another. e.g.: europe>france>paris'))?></small></td>
</tr>
<tr>
    <th></th>
    <td align="right"><small><?php echo T_('Note: use "=" to make synonym two tags. e.g.: france=frenchcountry')?></small></td>
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
        <input type="button" name="cancel" value="<?php echo T_('Cancel') ?>" onclick="<?php echo $popup?'window.close();':'javascript: history.go(-1)'; ?>" />
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

<link href="<?php echo ROOT ?>js/jquery-ui-1.8.5/themes/base/jquery.ui.all.css" rel="stylesheet" type="text/css"/>

<script type="text/javascript" src="<?php echo ROOT ?>js/jquery-ui-1.8.5/jquery.ui.core.js"></script>
<script type="text/javascript" src="<?php echo ROOT ?>js/jquery-ui-1.8.5/jquery.ui.widget.js"></script>
<script type="text/javascript" src="<?php echo ROOT ?>js/jquery-ui-1.8.5/jquery.ui.position.js"></script>
<script type="text/javascript" src="<?php echo ROOT ?>js/jquery-ui-1.8.5/jquery.ui.autocomplete.js"></script>
<script type="text/javascript">
jQuery(document).ready(function() {
    function split(val)
    {
        return val.split(/,\s*/);
    }

    function extractLast(term)
    {
        return split(term).pop();
    }
    //var availableTags = ["c++", "java", "php", "coldfusion", "javascript", "asp", "ruby"];

    jQuery("input#tags").autocomplete({

        minLength: 1,

        source: function(request, response) {
            // delegate back to autocomplete, but extract the last term
            response(
                /*
                $.ui.autocomplete.filter(
                    availableTags, extractLast(request.term)
                )
                */
                $.getJSON(
                    "<?php echo $ajaxUrl; ?>",
                    { beginsWith: extractLast(request.term) },
                    response
                )
            );
        },

        focus: function() {
            // prevent value inserted on focus
            return false;
        },
        select: function(event, ui) {
            var terms = split(this.value);
            // remove the current input
            terms.pop();
            // add the selected item
            terms.push(ui.item.value);
            // add placeholder to get the comma-and-space at the end
            terms.push("");
            this.value = terms.join(", ");
            return false;
        }
    });
});
</script>

<?php
// Dynamic tag selection
  //FIXME$this->includeTemplate('dynamictags.inc');

// Bookmarklets and import links
if (empty($_REQUEST['popup']) && (!isset($showdelete) || !$showdelete)) {
?>

<h3><?php echo T_('Bookmarklet'); ?></h3>
<p>
<script type="text/javascript">
//<![CDATA[
var browser=navigator.appName;
if (false && browser == "Opera") {
    document.write(
        <?php echo json_encode(
            sprintf(
                T_("Click one of the following bookmarklets to add a button you can click whenever you want to add the page you are on to %s"),
                $GLOBALS['sitename']
            )
        ); ?>
    );
} else if (false) {
    document.write(
        <?php echo json_encode(
            sprintf(
                T_("Drag one of the following bookmarklets to your browser's bookmarks and click it whenever you want to add the page you are on to %s"),
                $GLOBALS['sitename']
            )
        );
        ?>
    );
}
//]]>
</script>
:</p>
<script type="text/javascript">
//<![CDATA[
var selection = '';
if (window.getSelection) {
    selection = 'window.getSelection()';
} else if (document.getSelection) {
    selection = 'document.getSelection()';
} else if (document.selection) {
    selection = 'document.selection.createRange().text';
}
if (false && browser == "Opera")
    {
    document.write('<li><a class="bookmarklet" href="opera:/button/Go%20to%20page,%20%22javascript:x=document;a=encodeURIComponent(x.location.href);t=encodeURIComponent(x.title);d=encodeURIComponent('+selection+');location.href=\'<?php echo createURL('bookmarks', $GLOBALS['user']); ?>?action=add&amp;address=\'+a+\'&amp;title=\'+t+\'&amp;description=\'+d;void 0%22;,,%22Post%20to%20<?php echo jsEscTitle($GLOBALS['sitename']); ?>%22,%22Scuttle%22"><?php echo jsEscTitle(sprintf(T_('Post to %s'), $GLOBALS['sitename'])); ?><\/a><\/li>');
    document.write('<li><a class="bookmarklet" href="opera:/button/Go%20to%20page,%20%22javascript:x=document;a=encodeURIComponent(x.location.href);t=encodeURIComponent(x.title);d=encodeURIComponent('+selection+');open(\'<?php echo createURL('bookmarks', $GLOBALS['user']); ?>?action=add&amp;popup=1&amp;address=\'+a+\'&amp;title=\'+t+\'&amp;description=\'+d,\'<?php echo jsEscTitle($GLOBALS['sitename']); ?>\',\'modal=1,status=0,scrollbars=1,toolbar=0,resizable=1,width=790,height=465,left=\'+(screen.width-790)/2+\',top=\'+(screen.height-425)/2);void 0;%22,,%22Post%20to%20<?php echo urlencode($GLOBALS['sitename']); ?>%20(Pop-up)%22,%22Scuttle%22"><?php echo jsEscTitle(sprintf(T_('Post to %s (Pop-up)'), $GLOBALS['sitename'])); ?><\/a><\/li>');
    }
else if (false)
    {
    document.write('<li><a class="bookmarklet" href="javascript:x=document;a=encodeURIComponent(x.location.href);t=encodeURIComponent(x.title);d=encodeURIComponent('+selection+');location.href=\'<?php echo createURL('bookmarks', $GLOBALS['user']); ?>?action=add&amp;address=\'+a+\'&amp;title=\'+t+\'&amp;description=\'+d;void 0;"><?php echo jsEscTitle(sprintf(T_('Post to %s'), $GLOBALS['sitename'])); ?><\/a><\/li>');
    document.write('<li><a class="bookmarklet" href="javascript:x=document;a=encodeURIComponent(x.location.href);t=encodeURIComponent(x.title);d=encodeURIComponent('+selection+');open(\'<?php echo createURL('bookmarks', $GLOBALS['user']); ?>?action=add&amp;popup=1&amp;address=\'+a+\'&amp;title=\'+t+\'&amp;description=\'+d,\'<?php echo jsEscTitle($GLOBALS['sitename']); ?>\',\'modal=1,status=0,scrollbars=1,toolbar=0,resizable=1,width=790,height=465,left=\'+(screen.width-790)/2+\',top=\'+(screen.height-425)/2);void 0;"><?php echo jsEscTitle(sprintf(T_('Post to %s (Pop-up)'), $GLOBALS['sitename'])); ?><\/a><\/li>');
    }
//document.write('<\/ul>');
//]]>
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
