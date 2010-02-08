<?php
/**
 * Edits the common tag description
 *
 * SemanticScuttle - your social bookmark manager.
 *
 * PHP version 5.
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Benjamin Huynh-Kim-Bang <mensonge@users.sourceforge.net>
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @author   Eric Dane <ericdane@users.sourceforge.net>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */

require_once 'www-header.php';

/* Service creation: only useful services are created */
$b2tservice = SemanticScuttle_Service_Factory::get('Bookmark2Tag');
$cdservice  = SemanticScuttle_Service_Factory::get('CommonDescription');

/* Managing all possible inputs */
isset($_POST['confirm'])
    ? define('POST_CONFIRM', $_POST['confirm'])
    : define('POST_CONFIRM', '');
isset($_POST['cancel'])
    ? define('POST_CANCEL', $_POST['cancel'])
    : define('POST_CANCEL', '');
isset($_POST['description'])
    ? define('POST_DESCRIPTION', $_POST['description'])
    : define('POST_DESCRIPTION', '');
isset($_POST['referrer'])
    ? define('POST_REFERRER', $_POST['referrer'])
    : define('POST_REFERRER', '');


/* Managing current logged user */
$currentUser = $userservice->getCurrentObjectUser();

/* Managing path info */
list ($url, $tag) = explode('/', $_SERVER['PATH_INFO']);

//permissions
if (!$userservice->isLoggedOn()
    || (!$GLOBALS['enableCommonTagDescriptionEditedByAll']
        && !$currentUser->isAdmin()
    )
) {
    $tplVars['error'] = T_('Permission denied.');
    $templateservice->loadTemplate('error.500.tpl', $tplVars);
    exit();
}

$template = 'tagcommondescriptionedit.tpl';

if (POST_CONFIRM) {
    if (strlen($tag) > 0
        && $cdservice->addTagDescription($tag, stripslashes(POST_DESCRIPTION), $currentUser->getId(), time())
    ) {
        $tplVars['msg'] = T_('Tag common description updated');
        if (POST_REFERRER) {
            header('Location: '. POST_REFERRER);
        }
    } else {
        $tplVars['error'] = T_('Failed to update the tag common description');
        $template         = 'error.500.tpl';
    }
} else if (POST_CANCEL) {
    if (POST_REFERRER) {
        header('Location: '. POST_REFERRER);
    }
}

$tplVars['subtitle']    = T_('Edit Tag Common Description') .': '. $tag;
$tplVars['formaction']  = $_SERVER['SCRIPT_NAME'] .'/'. $tag;
$tplVars['referrer']    = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
$tplVars['tag']         = $tag;
$tplVars['description'] = $cdservice->getLastTagDescription($tag);
$templateservice->loadTemplate($template, $tplVars);
?>
