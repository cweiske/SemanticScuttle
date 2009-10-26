<?php
/* Define functions used into the application */


// Converts tags:
// - direction = out: convert spaces to underscores;
// - direction = in: convert underscores to spaces.
function convertTag($tag, $direction = 'out') {
	if ($direction == 'out') {
		$tag = str_replace(' ', '_', $tag);
	} else {
		$tag = str_replace('_', ' ', $tag);
	}
	return $tag;
}

function filter($data, $type = NULL) {
	if (is_string($data)) {
		$data = trim($data);
		$data = stripslashes($data);
		switch ($type) {
			case 'url':
				$data = rawurlencode($data);
				break;
			default:
				$data = htmlspecialchars($data);
				break;
		}
	} else if (is_array($data)) {
		foreach(array_keys($data) as $key) {
			$row =& $data[$key];
			$row = filter($row, $type);
		}
	}
	return $data;
}

function getPerPageCount($userObject = null) {
	global $defaultPerPage, $defaultPerPageForAdmins;
	
	if(isset($defaultPerPageForAdmins) && $userObject != null && $userObject->isAdmin()) {		
		return $defaultPerPageForAdmins;
	} else {
		return $defaultPerPage;
	}
}

function getSortOrder($override = NULL) {
	global $defaultOrderBy;

	if (isset($_GET['sort'])) {
		return $_GET['sort'];
	} else if (isset($override)) {
		return $override;
	} else {
		return $defaultOrderBy;
	}
}

function multi_array_search($needle, $haystack) {
	if (is_array($haystack)) {
		foreach(array_keys($haystack) as $key) {
			$value =& $haystack[$key];
			$result = multi_array_search($needle, $value);
			if (is_array($result)) {
				$return = $result;
				array_unshift($return, $key);
				return $return;
			} elseif ($result == true) {
				$return[] = $key;
				return $return;
			}
		}
		return false;
	} else {
		if ($needle === $haystack) {
			return true;
		} else {
			return false;
		}
	}
}

function createURL($page = '', $ending = '') {
	global $cleanurls;
	if (!$cleanurls && $page != '') {
		$page .= '.php';
	}
	if(strlen($ending)>0) {
		return ROOT . $page .'/'. $ending;
	} else {
		return ROOT . $page;
	}
}
/**
 * Creates a "vote for/against this bookmark" URL.
 * Also runs htmlspecialchars() on them to prevent XSS.
 * We need to use ENT_QUOTES since otherwise we would not be
 * protected when the attribute is used in single quotes.
 *
 * @param boolean $for For the bookmark (true) or against (false)
 * @param integer $bId Bookmark ID
 *
 * @return string URL to use
 */
function createVoteURL($for, $bId)
{
    //FIXME: we need a "current url" variable that is
    //filled with a safe version of the current url.
    //all this specialchars stuff is bit of a hack.
    return htmlspecialchars(
        createURL(
            'vote',
            ($for ? 'for' : 'against') . '/' . $bId
        ) . '?from=' . urlencode($_SERVER['REQUEST_URI']),
        ENT_QUOTES
    );
}

/* Shorten a string like a URL for example by cutting the middle of it */
function shortenString($string, $maxSize=75) {
	$output = '';
	if(strlen($string) > $maxSize) {
		$output = substr($string, 0, $maxSize/2).'...'.substr($string, -$maxSize/2);
	} else {
		$output = $string;
	}
	return $output;
}

/* Check url format and check online if the url is a valid page (Not a 404 error for example) */
function checkUrl($url, $checkOnline = true) {
	//check format
	if(!preg_match("#(ht|f)tp(s?)\://\S+\.\S+#i",$url)) {
		return false;
	}

	if($checkOnline) {
		//look if the page doesn't return a void or 40X or 50X HTTP code error
		$h = @get_headers($url);
		if(is_array($h) && strpos($h[0], '40') === false && strpos($h[0], '50') === false) {
			return true;
		} else {
			return false;
		}
	} else {
		return true;
	}
}

/* Returns a concatenated String
 * including all the tags from the array $arrayTags (excepted of the $exceptedTag)
 * separated by the $separator.
 * */
function aggregateTags($arrayTags, $separator = ' + ', $exceptedTag = '') {
	$output = '';

	for($i = 0; $i<count($arrayTags); $i++) {
		if($arrayTags[$i] != $exceptedTag) {
			$output.= $arrayTags[$i] . $separator;
		}
	}
	return substr($output, 0, strlen($output) - strlen($separator) );
}

function message_die($msg_code, $msg_text = '', $msg_title = '', $err_line = '', $err_file = '', $sql = '', $db = NULL) {
	if(defined('HAS_DIED'))
	die(T_('message_die() was called multiple times.'));
	define('HAS_DIED', 1);

	$sql_store = $sql;

	// Get SQL error if we are debugging. Do this as soon as possible to prevent
	// subsequent queries from overwriting the status of sql_error()
	if (DEBUG_MODE && ($msg_code == GENERAL_ERROR || $msg_code == CRITICAL_ERROR)) {
		$sql_error = is_null($db) ? '' : $db->sql_error();
		$debug_text = '';

		if ($sql_error['message'] != '')
		$debug_text .= '<br /><br />'. T_('SQL Error') .' : '. $sql_error['code'] .' '. $sql_error['message'];

		if ($sql_store != '')
		$debug_text .= '<br /><br />'. $sql_store;

		if ($err_line != '' && $err_file != '')
		$debug_text .= '</br /><br />'. T_('Line') .' : '. $err_line .'<br />'. T_('File') .' :'. $err_file;

		debug_print_backtrace();
	}

	switch($msg_code) {
		case GENERAL_MESSAGE:
			if ($msg_title == '')
			$msg_title = T_('Information');
			break;

		case CRITICAL_MESSAGE:
			if ($msg_title == '')
			$msg_title = T_('Critical Information');
			break;

		case GENERAL_ERROR:
			if ($msg_text == '')
			$msg_text = T_('An error occured');

			if ($msg_title == '')
			$msg_title = T_('General Error');
			break;

		case CRITICAL_ERROR:
			// Critical errors mean we cannot rely on _ANY_ DB information being
			// available so we're going to dump out a simple echo'd statement

			if ($msg_text == '')
			$msg_text = T_('An critical error occured');

			if ($msg_title == '')
			$msg_title = T_('Critical Error');
			break;
	}

	// Add on DEBUG_MODE info if we've enabled debug mode and this is an error. This
	// prevents debug info being output for general messages should DEBUG_MODE be
	// set TRUE by accident (preventing confusion for the end user!)
	if (DEBUG_MODE && ($msg_code == GENERAL_ERROR || $msg_code == CRITICAL_ERROR)) {
		if ($debug_text != '')
		$msg_text = $msg_text . '<br /><br /><strong>'. T_('DEBUG MODE') .'</strong>'. $debug_text;
	}

	echo "<html>\n<body>\n". $msg_title ."\n<br /><br />\n". $msg_text ."</body>\n</html>";
	exit;
}
?>
