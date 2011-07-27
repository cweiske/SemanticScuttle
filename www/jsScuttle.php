<?php
$GLOBALS['saveInLastUrl'] = false;
$httpContentType = 'text/javascript';
require_once 'www-header.php';
require_once 'SemanticScuttle/functions.php';
$theme = new SemanticScuttle_Model_Theme($GLOBALS['theme']);
$player_root = ROOT .'includes/player/';
?>

function _playerAdd(anchor) {
    var url = anchor.href;
    var code = '<object type="application/x-shockwave-flash" data="<?php echo $player_root ?>musicplayer_f6.swf?song_url=' + url +'&amp;b_bgcolor=ffffff&amp;b_fgcolor=000000&amp;b_colors=0000ff,0000ff,ff0000,ff0000&buttons=<?php echo $player_root ?>load.swf,<?php echo $player_root ?>play.swf,<?php echo $player_root ?>stop.swf,<?php echo $player_root ?>error.swf" width="14" height="14">';
    var code = code + '<param name="movie" value="<?php echo $player_root ?>musicplayer.swf?song_url=' + url +'&amp;b_bgcolor=ffffff&amp;b_fgcolor=000000&amp;b_colors=0000ff,0000ff,ff0000,ff0000&amp;buttons=<?php echo $player_root ?>load.swf,<?php echo $player_root ?>play.swf,<?php echo $player_root ?>stop.swf,<?php echo $player_root ?>error.swf" />';
    var code = code + '</object>';
    anchor.parentNode.innerHTML = code +' '+ anchor.parentNode.innerHTML;
}

String.prototype.trim = function() {
    return this.replace(/^\s+|\s+$/g, '');
};

var deleted = false;
function deleteBookmark(ele, input){
    var confirmDelete = "<span><?php echo T_('Are you sure?') ?> <a href=\"#\" onclick=\"deleteConfirmed(this, " + input + ", \'\'); return false;\"><?php echo T_('Yes'); ?></a> - <a href=\"#\" onclick=\"deleteCancelled(this); return false;\"><?php echo T_('No'); ?></a></span>";
    ele.style.display = 'none';    
    ele.parentNode.innerHTML = ele.parentNode.innerHTML + confirmDelete;
}

function deleteCancelled(ele) {
    var del = previousElement(ele.parentNode);
    del.style.display = 'inline';
    ele.parentNode.parentNode.removeChild(ele.parentNode);
    return false;
}

function deleteConfirmed(ele, input, response) {
    //if (deleted == false) {
        deleted = ele.parentNode.parentNode.parentNode.parentNode;
    //}    
    var post = deleted;
    post.className = 'xfolkentry deleted';    
    if (response != '') {
        post.style.display = 'none';
        deleted = false;
    } else {
        loadXMLDocProc('<?php echo ROOT; ?>ajaxDelete.php?id=' + input);        
        post.style.display = 'none';        
    }
}

function previousElement(ele) {
    ele = ele.previousSibling;
    while (ele.nodeType != 1) {
        ele = ele.previousSibling;
    }
    return ele;
}

function isAvailable(input, response){
    var usernameField = document.getElementById("username");
    var username = usernameField.value;
    username = username.toLowerCase();
    username = username.trim();
    var availability = document.getElementById("availability");
    if (username != '') {
        usernameField.style.backgroundImage = 'url(<?php echo $theme->resource('images/loading.gif'); ?>)';
        if (response != '') {
            usernameField.style.backgroundImage = 'none';
            if (response == 'true') {
                availability.className = 'available';
                availability.innerHTML = '<?php echo T_('Available'); ?>';
            } else {
                availability.className = 'not-available';
                availability.innerHTML = '<?php echo T_('Not Available'); ?>';
            }
        } else {
            loadXMLDoc('<?php echo ROOT; ?>ajaxIsAvailable.php?username=' + username);
        }
    }
}

function useAddress(ele) {
    var address = ele.value;
    if (address != '') {
        if (address.indexOf(':') < 0) {
            address = 'http:\/\/' + address;
        }
        getTitle(address, null);
        ele.value = address;
    }
}

/**
 * Makes an ajax call to PHP script to generate an new Private Key
 *
 * @param input    Calling object
 * @param response Response object that returned value is placed
 *
 * @return boolean Returns false to halt execution after call
 */
function getNewPrivateKey(input, response){
    var pk = document.getElementById('pPrivateKey');
    if (response != null) {
        pk.value = response.trim();
    } else {
        loadXMLDocProc('<?php echo ROOT; ?>ajaxGetNewPrivateKey.php');
    }
    return false;
}

function getTitle(input, response){
    var title = document.getElementById('titleField');
    if (title.value == '') {
        title.style.backgroundImage = 'url(<?php echo $theme->resource('images/loading.gif');?>)';
        if (response != null) {
            title.style.backgroundImage = 'none';
            title.value = response;
        } else if (input.indexOf('http') > -1) {
            loadXMLDocProc('<?php echo ROOT; ?>ajaxGetTitle.php?url=' + input);
        } else {
            return false;
        }
    }
}

var xmlhttp;
function loadXMLDocProc(url) {
    loadXMLDoc(url, processStateChange);
}
function vote(bookmark, vote) {
    if (vote == 1) {
        vote = 'for';
    } else {
        vote = 'against';
    }
    loadXMLDoc(
        '<?php echo ROOT; ?>ajaxVote.php/' + vote + '/' + bookmark,
        processVotingResult
    );
}
function loadXMLDoc(url, callback) {
    // Native
    if (window.XMLHttpRequest) {
        xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = callback;
        xmlhttp.open("GET", url, true);
        xmlhttp.send(null);
    // ActiveX
    } else if (window.ActiveXObject) {
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        if (xmlhttp) {
            xmlhttp.onreadystatechange = processStateChange;
            xmlhttp.open("GET", url, true);
            xmlhttp.send();
        }
    }
}

function processStateChange() {
    if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
    	response = xmlhttp.responseXML.documentElement;
        method = response.getElementsByTagName('method')[0].firstChild.data;
        result = response.getElementsByTagName('result')[0].firstChild.data;
        eval(method + '(\'\', result)');
    }
}
function processVotingResult() {
    if (xmlhttp.readyState != 4 || xmlhttp.status != 200) {
        return;
    }
    var response = xmlhttp.responseXML.documentElement;
    var bookmark = response.getElementsByTagName('bookmark')[0]
        .firstChild.nodeValue;
    var bmnode = document.getElementById('bmv-'+bookmark);

    bmnode.parentNode.replaceChild(
        xmlhttp.responseXML.importNode(
            response.getElementsByTagName('html')[0].firstChild,
            true
        ),
        bmnode
    );
}

function playerLoad() {
    var anchors = document.getElementsByTagName('a');
    var anchors_length = anchors.length;
    for (var i = 0; i < anchors_length; i++) {
        if (anchors[i].className == 'taggedlink' && anchors[i].href.match(/\.mp3$/i)) {
            _playerAdd(anchors[i]);
        }
    }
}

function addAnchor(anchorForm, where) {
    var whereZone = document.getElementById(where);    
    whereZone.value = whereZone.value + anchorForm;    
    document.getElementById(where).focus();
}
