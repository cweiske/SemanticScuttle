<?php
/***************************************************************************
 Copyright (C) 2005 - 2006 Scuttle project
 http://sourceforge.net/projects/scuttle/
 http://scuttle.org/

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 ***************************************************************************/

/* Service creation: only useful services are created */
$b2tservice = SemanticScuttle_Service_Factory :: get('Bookmark2Tag');

$logged_on_userid = $userservice->getCurrentUserId();

//tags from current user
$userPopularTags        =& $b2tservice->getPopularTags($logged_on_userid, 25, $logged_on_userid);
$userPopularTagsCloud   =& $b2tservice->tagCloud($userPopularTags, 5, 90, 175);
$userPopularTagsCount   = count($userPopularTags);

//tags from all users
$allPopularTags        =& $b2tservice->getPopularTags(null, 5, $logged_on_userid);
$allPopularTagsCloud   =& $b2tservice->tagCloud($allPopularTags, 5, 90, 175);
$allPopularTagsCount   = count($allPopularTags);


// function printing the cloud
function writeTagsProposition($tagsCloud, $title)
{
    static $id = 0;
    ++$id;

    echo <<<JS
    $('.edit-tagclouds')
        .append(
'<div class="collapsible" id="edit-tagcloud-$id">'
+ '  <h3>$title</h3>'
+ '  <p class="popularTags tags"></p>'
+ '</div>');
JS;
	
	$taglist = '';
	foreach (array_keys($tagsCloud) as $key) {
	    $row = $tagsCloud[$key];
	    $entries = T_ngettext('bookmark', 'bookmarks', $row['bCount']);
	    $taglist .= '<span'
            . ' title="'. $row['bCount'] . ' ' . $entries . '"'
            . ' style="font-size:' . $row['size'] . '"'
            . ' onclick="addTag(this)">'
            . filter($row['tag'])
            . '</span> ';
	}
    echo '$(\'#edit-tagcloud-' . $id . ' p\').append('
        . json_encode($taglist)
        . ");\n";
}


if ($allPopularTagsCount > 0 || $userPopularTagsCount > 0 ) { ?>
<script type="text/javascript">
//<![CDATA[
Array.prototype.contains = function (ele) {
    for (var i = 0; i < this.length; i++) {
        if (this[i] == ele) {
            return true;
        }
    }
    return false;
};

Array.prototype.remove = function (ele) {
    var arr = new Array();
    var count = 0;
    for (var i = 0; i < this.length; i++) {
        if (this[i] != ele) {
            arr[count] = this[i];
            count++;
        }
    }
    return arr;
};

function addonload(addition) {
    //var existing = window.onload;
    window.onload = function () {
        //existing();
        addition();
    }
}

jQuery(function($) {
<?php
if ($userPopularTagsCount > 0) {
	writeTagsProposition($userPopularTagsCloud, T_('Popular Tags'));
}
if ($allPopularTagsCount > 0) {
	writeTagsProposition($allPopularTagsCloud, T_('Popular Tags From All Users'));
}
?>
        var taglist = $('#tags');
        var tags = taglist.val().split(', ');
        
        var populartags = $('.edit-tagclouds span');
        
        for (var i = 0; i < populartags.length; i++) {
            if (tags.contains(populartags[i].innerHTML)) {
                populartags[i].className = 'selected';
            }
        }
});

function addTag(ele) {
    var thisTag = ele.innerHTML;
    var taglist = document.getElementById('tags');
    var tags = taglist.value.split(', ');
    
    // If tag is already listed, remove it
    if (tags.contains(thisTag)) {
        tags = tags.remove(thisTag);
        ele.className = 'unselected';
        
    // Otherwise add it
    } else {
        tags.splice(0, 0, thisTag);
        ele.className = 'selected';
    }
    
    taglist.value = tags.join(', ');
    
    document.getElementById('tags').focus();
}
//]]>
</script>
<div class="edit-tagclouds"></div>
<?php
}
?>
