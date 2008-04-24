<?php
$userservice =& ServiceFactory::getServiceInstance('UserService');
$bookmarkservice =& ServiceFactory::getServiceInstance('BookmarkService');
$tagservice =& ServiceFactory::getServiceInstance('TagService');
$cdservice =& ServiceFactory::getServiceInstance('CommonDescriptionService');

$logged_on_userid = $userservice->getCurrentUserId();
$currentUser = $userservice->getCurrentUser();
$currentUsername = $currentUser[$userservice->getFieldName('username')];

$this->includeTemplate($GLOBALS['top_include']);

include('search.inc.php');
?>

<?php 
if((isset($currenttag) && $GLOBALS['enableCommonTagDescription'])
 || (isset($hash) && $GLOBALS['enableCommonBookmarkDescription'])):?>
<p class="commondescription">

<?php
if(isset($currenttag) && $cdservice->getLastTagDescription($currenttag)) {
    $description = $cdservice->getLastTagDescription($currenttag);
    echo nl2br(filter($description['cdDescription']));
} elseif(isset($hash) && $cdservice->getLastBookmarkDescription($hash)) {
    $description = $cdservice->getLastBookmarkDescription($hash);
    echo nl2br(filter($description['cdTitle'])). "<br/>";
    echo nl2br(filter($description['cdDescription'])). "<br/>";
}

if($logged_on_userid>0) {
    if(isset($currenttag)) {
	echo ' (<a href="'. createURL('tagcommondescriptionedit', $currenttag).'">';
	echo T_('edit common description').'</a>)';
    } elseif(isset($hash)) {
	echo ' (<a href="'.createURL('bookmarkcommondescriptionedit', $hash).'">';
	echo T_('edit common description').'</a>)';
    }
}
?>
</p>
<?php endif ?>


<?php
$userObject = $userservice->getUserByUsername($user);
/* Private tag description */
if(isset($currenttag) && strlen($user)>0 && $tagservice->getDescription($currenttag, $userObject['uId'])):?>
<p class="commondescription">
<?php

    $description = $tagservice->getDescription($currenttag, $userObject['uId']);
    echo nl2br(filter($description['tDescription']));
?>
</p>
<?php endif ?>

<?php if (count($bookmarks) > 0) { ?>
<script type="text/javascript">
window.onload = playerLoad;
</script>

<p id="sort">
    <?php echo $total.' '.T_("bookmark(s)"); ?> - 
    <?php echo T_("Sort by:"); ?>
    <a href="?sort=date_desc"><?php echo T_("Date"); ?></a><span> / </span>
    <a href="?sort=title_asc"><?php echo T_("Title"); ?></a><span> / </span>
    <?php
    if (!isset($hash)) {
    ?>
    <a href="?sort=url_asc"><?php echo T_("URL"); ?></a>
    <?php
    }
    ?>

    <?php
    if(isset($currenttag)) {
	if(isset($user)) {
	    echo ' - ';
	    echo '<a href="'. createURL('tags', $currenttag) .'">';
	    echo T_('Bookmarks from other users for this tag').'</a>';
	    //echo T_(' for these tags');
 	} else if($logged_on_userid>0){
	    echo ' - ';
	    echo '<a href="'. createURL('bookmarks', $currentUsername.'/'.$currenttag) .'">';
	    echo T_('Only your bookmarks for this tag').'</a>';
	    //echo T_(' for these tags');
	}
    }
    ?>
</p>



<ol<?php echo ($start > 0 ? ' start="'. ++$start .'"' : ''); ?> id="bookmarks">

    <?php
    foreach(array_keys($bookmarks) as $key) {
        $row =& $bookmarks[$key];
        switch ($row['bStatus']) {
            case 0:
                $access = '';
                break;
            case 1:
                $access = ' shared';
                break;
            case 2:
                $access = ' private';
                break;
        }

        $cats = '';
	$tagsForCopy = '';
        $tags = $row['tags'];
        foreach(array_keys($tags) as $key) {

            $tag =& $tags[$key];
            $cats .= '<a href="'. sprintf($cat_url, filter($row['username'], 'url'), filter($tag, 'url')) .'" rel="tag">'. filter($tag) .'</a>, ';
	    $tagsForCopy.= $tag.',';
        }
        $cats = substr($cats, 0, -2);
        if ($cats != '') {
            $cats = ' to '. $cats;
        }

        // Edit and delete links
        $edit = '';
        if ($bookmarkservice->editAllowed($row['bId'])) {
            $edit = ' - <a href="'. createURL('edit', $row['bId']) .'">'. T_('Edit') .'</a><script type="text/javascript">document.write(" - <a href=\"#\" onclick=\"deleteBookmark(this, '. $row['bId'] .'); return false;\">'. T_('Delete') .'<\/a>");</script>';
        }

        // User attribution
        $copy = '';
        if (!isset($user) || isset($watched)) {
            $copy = ' '. T_('by') .' <a href="'. createURL('bookmarks', $row['username']) .'">'. $row['username'] .'</a>';
        }

        // Udders!
        if (!isset($hash)) {
            $others = $bookmarkservice->countOthers($row['bAddress']);
            $ostart = '<a href="'. createURL('history', $row['bHash']) .'">';
            $oend = '</a>';
            switch ($others) {
                case 0:
                    break;
                case 1:
                    $copy .= sprintf(T_(' and %s1 other%s'), $ostart, $oend);
                    break;
                default:
                    $copy .= sprintf(T_(' and %2$s%1$s others%3$s'), $others, $ostart, $oend);
            }
        }

        // Copy link
        if ($userservice->isLoggedOn() && ($logged_on_userid != $row['uId']) && !$bookmarkservice->bookmarkExists($row['bAddress'], $logged_on_userid)) {
            // Get the username of the current user
            $currentUser = $userservice->getCurrentUser();
            $currentUsername = $currentUser[$userservice->getFieldName('username')];
            $copy .= ' - <a href="'. createURL('bookmarks', $currentUsername .'?action=add&amp;address='. urlencode($row['bAddress']) .'&amp;title='. urlencode($row['bTitle'])). '&amp;description='.urlencode($row['bDescription']). '&amp;tags='.$tagsForCopy  .'">'. T_('Copy') .'</a>';   
        }

        // Nofollow option
        $rel = '';
        if ($GLOBALS['nofollow']) {
            $rel = ' rel="nofollow"';
        }

        $address = filter($row['bAddress']);
        
        // Redirection option
        if ($GLOBALS['useredir']) {
            $address = $GLOBALS['url_redir'] . $address;
        }
        
        // Output
        echo '<li class="xfolkentry'. $access .'">'."\n";
	if ($GLOBALS['enableWebsiteThumbnails']) {
	    echo '<a href="'. $address .'"'. $rel .' ><img class="thumbnail" src="http://www.artviper.net/screenshots/screener.php?sdx=1024&sdy=768&w=90&h=68&url='.$address.'"></a>';
	}
	echo '<div>';

        echo '<div class="link"><a href="'. $address .'"'. $rel .' class="taggedlink">'. filter($row['bTitle']) ."</a></div>\n";
        if ($row['bDescription'] == '') {
            $row['bDescription'] = '-';
        }
        echo '<div class="description">'. filter($row['bDescription']) ."</div>\n";
	if(!isset($hash)) {
	    echo '<div class="address">'.shortenString($address).'</div>';
	}

        echo '<div class="meta">'. date($GLOBALS['shortdate'], strtotime($row['bDatetime'])) . $cats . $copy . $edit ."</div>\n";

	echo '</div>';

        echo "</li>\n";
    }
    ?>

</ol>

    <?php
    // PAGINATION
    
    // Ordering
    $sortOrder = '';
    if (isset($_GET['sort'])) {
        $sortOrder = 'sort='. $_GET['sort'];
    }
    
    $sortAmp = (($sortOrder) ? '&amp;'. $sortOrder : '');
    $sortQue = (($sortOrder) ? '?'. $sortOrder : '');
    
    // Previous
    $perpage = getPerPageCount();
    if (!$page || $page < 2) {
        $page = 1;
        $start = 0;
        $bfirst = '<span class="disable">'. T_('First') .'</span>';
        $bprev = '<span class="disable">'. T_('Previous') .'</span>';
    } else {
        $prev = $page - 1;
        $prev = 'page='. $prev;
        $start = ($page - 1) * $perpage;
        $bfirst= '<a href="'. sprintf($nav_url, $user, $currenttag, '') . $sortQue .'">'. T_('First') .'</a>';
        $bprev = '<a href="'. sprintf($nav_url, $user, $currenttag, '?') . $prev . $sortAmp .'">'. T_('Previous') .'</a>';
    }
    
    // Next
    $next = $page + 1;
    $totalpages = ceil($total / $perpage);
    if (count($bookmarks) < $perpage || $perpage * $page == $total) {
        $bnext = '<span class="disable">'. T_('Next') .'</span>';
        $blast = '<span class="disable">'. T_('Last') ."</span>\n";
    } else {
        $bnext = '<a href="'. sprintf($nav_url, $user, $currenttag, '?page=') . $next . $sortAmp .'">'. T_('Next') .'</a>';
        $blast = '<a href="'. sprintf($nav_url, $user, $currenttag, '?page=') . $totalpages . $sortAmp .'">'. T_('Last') ."</a>\n";
    }

    // RSS
    $brss = '';
    $size = count($rsschannels);
    for ($i = 0; $i < $size; $i++) {
        $brss =  '<a style="background:#FFFFFF" href="'. $rsschannels[$i][1] .'" title="'. $rsschannels[$i][0] .'"><img src="'. $GLOBALS['root'] .'rss.gif" width="16" height="16" alt="'. $rsschannels[$i][0] .'" /></a>'; 
    }

    echo '<p class="paging">'. $bfirst .'<span> / </span>'. $bprev .'<span> / </span>'. $bnext .'<span> / </span>'. $blast .'<span> / </span>'. sprintf(T_('Page %d of %d'), $page, $totalpages) ." ". $brss ." </p>\n";


    

} else {
    echo '<p class="error">'.T_('No bookmarks available').'</p>';
}
$this->includeTemplate('sidebar.tpl');
$this->includeTemplate($GLOBALS['bottom_include']);
?>
