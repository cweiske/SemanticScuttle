<?php

/* Service creation: only useful services are created */
$bookmarkservice =& ServiceFactory::getServiceInstance('BookmarkService');
$tagservice =& ServiceFactory::getServiceInstance('TagService');
$cdservice =& ServiceFactory::getServiceInstance('CommonDescriptionService');


$pageName = isset($pageName)?$pageName:"";
$user = isset($user)?$user:"";
$currenttag = isset($currenttag)?$currenttag:"";


$this->includeTemplate($GLOBALS['top_include']);

include('search.inc.php');
?>

<?php if($pageName == PAGE_INDEX):?>
<p id="welcome"><?php echo $GLOBALS['welcomeMessage'];?></p>
<?php endif?>


<?php if($GLOBALS['enableAdminColors']!=false && isset($userid) && $userservice->isAdmin($userid)): ?>
<div style="width:70%;text-align:center;">
<img src="<?php echo ROOT ?>images/logo_24.gif" width="12px"/> <?php echo T_('Bookmarks on this page are managed by an admin user.'); ?><img src="<?php echo ROOT ?>images/logo_24.gif" width="12px"/>
</div>
<?php endif?>


<?php
// common tag description
if(($currenttag!= '' && $GLOBALS['enableCommonTagDescription'])
|| (isset($hash) && $GLOBALS['enableCommonBookmarkDescription'])):?>


<p class="commondescription"><?php
if($currenttag!= '' && $cdservice->getLastTagDescription($currenttag)) {
	$description = $cdservice->getLastTagDescription($currenttag);
	echo nl2br(filter($description['cdDescription']));
} elseif(isset($hash) && $cdservice->getLastBookmarkDescription($hash)) {
	$description = $cdservice->getLastBookmarkDescription($hash);
	echo nl2br(filter($description['cdTitle'])). "<br/>";
	echo nl2br(filter($description['cdDescription'])). "<br/>";
}

//common tag description edit
if($userservice->isLoggedOn()) {
	if($currenttag!= '') {
		echo ' <a href="'. createURL('tagcommondescriptionedit', $currenttag).'">';
		echo T_('common description').' <img src="'.ROOT.'images/b_edit.png" /></a>';
	} elseif(isset($hash)) {
		echo ' (<a href="'.createURL('bookmarkcommondescriptionedit', $hash).'">';
		echo T_('edit common description').'</a>)';
	}
}
?></p>
<?php endif ?>


<?php
/* personal tag description */
if($currenttag!= '' && $user!='') {
	$userObject = $userservice->getUserByUsername($user);
	if($tagservice->getDescription($currenttag, $userObject['uId'])) { ?>

<p class="commondescription"><?php
$description = $tagservice->getDescription($currenttag, $userObject['uId']);
echo nl2br(filter($description['tDescription']));

//personal tag description edit
if($userservice->isLoggedOn()) {
	if($currenttag!= '') {
		echo ' <a href="'. createURL('tagedit', $currenttag).'">';
		echo T_('personal description').' <img src="'.ROOT.'images/b_edit.png" /></a>';
	}
}
?></p>

<?php
	}
}
?>

<?php if (count($bookmarks) > 0) { ?>
<script type="text/javascript">
window.onload = playerLoad;
</script>

<p id="sort"><?php echo $total.' '.T_("bookmark(s)"); ?> - <?php echo T_("Sort by:"); ?>
<?php
$dateSort = (getSortOrder()=='date_desc')? 'date_asc':'date_desc';
$titleSort = (getSortOrder()=='title_asc')? 'title_desc':'title_asc';
$urlSort = (getSortOrder()=='url_asc')? 'url_desc':'url_asc';
?> <a href="?sort=<?php echo $dateSort ?>"><?php echo T_("Date"); ?></a><span>
/ </span> <a href="?sort=<?php echo $titleSort ?>"><?php echo T_("Title"); ?></a><span>
/ </span> <?php
if (!isset($hash)) {
	?> <a href="?sort=<?php echo $urlSort ?>"><?php echo T_("URL"); ?></a>
	<?php
}
?> <?php
if($currenttag!= '') {
	if($user!= '') {
		echo ' - ';
		echo '<a href="'. createURL('tags', $currenttag) .'">';
		echo T_('Bookmarks from other users for this tag').'</a>';
		//echo T_(' for these tags');
	} else if($userservice->isLoggedOn()){
		echo ' - ';
		echo '<a href="'. createURL('bookmarks', $currentUser->getUsername().'/'.$currenttag) .'">';
		echo T_('Only your bookmarks for this tag').'</a>';
		//echo T_(' for these tags');
	}
}
?></p>



<ol <?php echo ($start > 0 ? ' start="'. ++$start .'"' : ''); ?>
	id="bookmarks">

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
			$cats = ' '.T_('in').' '. $cats;
		}

		// Edit and delete links
		$edit = '';
		if ($bookmarkservice->editAllowed($row['bId'])) {
			$edit = ' - <a href="'. createURL('edit', $row['bId']) .'">'. T_('Edit') .'</a><script type="text/javascript">document.write(" - <a href=\"#\" onclick=\"deleteBookmark(this, '. $row['bId'] .'); return false;\">'. T_('Delete') .'<\/a>");</script>';
		}

		// User attribution
		$copy = '';
		if ($user == '' || isset($watched)) {
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
		if ($userservice->isLoggedOn()
		&& ($currentUser->getId() != $row['uId'])
		&& !$bookmarkservice->bookmarkExists($row['bAddress'], $currentUser->getId())) {
			$copy .= ' - <a href="'. createURL('bookmarks', $currentUser->getUsername() .'?action=add&amp;address='. urlencode($row['bAddress']) .'&amp;title='. urlencode($row['bTitle'])). '&amp;description='.urlencode($row['bDescription']). '&amp;tags='.$tagsForCopy  .'">'. T_('Copy') .'</a>';
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
		
		// Admin specific design
		if($userservice->isAdmin($row['uId'])) {
			$adminBgClass = 'class="adminBackground"';
			$adminStar = ' <img src="'. ROOT .'images/logo_24.gif" width="12px" title="'. T_('This bookmark is certified by an admin user.') .'" />';
		} else {
			$adminBgClass = '';
			$adminStar = '';
		}

		// Output
		echo '<li class="xfolkentry'. $access .'" >'."\n";
		if ($GLOBALS['enableWebsiteThumbnails']) {
			$thumbnailHash = md5($address.$GLOBALS['thumbnailsUserId'].$GLOBALS['thumbnailsKey']);
			//echo '<a href="'. $address .'"'. $rel .' ><img class="thumbnail" src="http://www.artviper.net/screenshots/screener.php?url='.$address.'&w=120&sdx=1280&userID='.$GLOBALS['thumbnailsUserId'].'&hash='.$thumbnailHash.'" />';
			echo '<img class="thumbnail" onclick="window.location.href=\''.$address.'\'" src="http://www.artviper.net/screenshots/screener.php?url='.$address.'&w=120&sdx=1280&userID='.$GLOBALS['thumbnailsUserId'].'&hash='.$thumbnailHash.'" />';
		}
		
		echo '<div '.$adminBgClass.' >';;

		echo '<div class="link"><a href="'. $address .'"'. $rel .' class="taggedlink">'. filter($row['bTitle']) ."</a>" . $adminStar . "</div>\n";
		if ($row['bDescription'] == '') {
			$bkDescription = '-';
		} else {
			// Improve description display (anchors, links, ...)
			$bkDescription = preg_replace('|\[\/.*?\]|', '', filter($row['bDescription'])); // remove final anchor
			$bkDescription = preg_replace('|\[(.*?)\]|', ' <b>$1 </b>', $bkDescription); // highlight starting anchor
			$bkDescription = preg_replace('@((http|https|ftp)://.*?)( |\r|$)@', '<a href="$1">$1</a>$3', $bkDescription); // make url clickable
			
		}
		echo '<div class="description">'. nl2br($bkDescription) ."</div>\n";
		//if(!isset($hash)) {
			echo '<div class="address">'.shortenString($address).'</div>';
		//}

		echo '<div class="meta">'. date($GLOBALS['shortdate'], strtotime($row['bModified'])) . $cats . $copy . $edit ."</div>\n";

		echo '</div>';

		echo "</li>\n";
	}
	?>

</ol>

	<?php
	// PAGINATION

	// Ordering
	$sortOrder = '';
	if (GET_SORT != '') {
		$sortOrder = 'sort='. GET_SORT;
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
		$brss =  '<a style="background:#FFFFFF" href="'. $rsschannels[$i][1] .'" title="'. $rsschannels[$i][0] .'"><img src="'. ROOT .'images/rss.gif" width="16" height="16" alt="'. $rsschannels[$i][0] .'" /></a>';
	}

	echo '<p class="paging">'. $bfirst .'<span> / </span>'. $bprev .'<span> / </span>'. $bnext .'<span> / </span>'. $blast .'<span> / </span>'. sprintf(T_('Page %d of %d'), $page, $totalpages) ." ". $brss ." </p>\n";




} else {
	echo '<p class="error">'.T_('No bookmarks available').'</p>';
}
$this->includeTemplate('sidebar.tpl');
$this->includeTemplate($GLOBALS['bottom_include']);
?>
