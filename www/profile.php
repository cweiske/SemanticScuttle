<?php
/***************************************************************************
 Copyright (C) 2004 - 2006 Scuttle project
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

require_once 'www-header.php';

/* Service creation: only useful services are created */
// No specific services

/* Managing all possible inputs */
isset($_POST['submitted']) ? define('POST_SUBMITTED', $_POST['submitted']): define('POST_SUBMITTED', '');
isset($_POST['pPass']) ? define('POST_PASS', $_POST['pPass']): define('POST_PASS', '');
isset($_POST['pPassConf']) ? define('POST_PASSCONF', $_POST['pPassConf']): define('POST_PASSCONF', '');
isset($_POST['pName']) ? define('POST_NAME', $_POST['pName']): define('POST_NAME', '');
isset($_POST['pMail']) ? define('POST_MAIL', $_POST['pMail']): define('POST_MAIL', '');
isset($_POST['pPage']) ? define('POST_PAGE', $_POST['pPage']): define('POST_PAGE', '');
isset($_POST['pDesc']) ? define('POST_DESC', $_POST['pDesc']): define('POST_DESC', '');

isset($_POST['token']) ? define('POST_TOKEN', $_POST['token']): define('POST_TOKEN', '');
isset($_SESSION['token']) ? define('SESSION_TOKEN', $_SESSION['token']): define('SESSION_TOKEN', '');
isset($_SESSION['token_stamp']) ? define('SESSION_TOKENSTAMP', $_SESSION['token_stamp']): define('SESSION_TOKENSTAMP', '');


@list($url, $user) = isset($_SERVER['PATH_INFO']) ? explode('/', $_SERVER['PATH_INFO']) : null;

if ($user) {
    if (is_int($user)) {
        $userid = intval($user);
    } else {
        $user = urldecode($user);
        $userinfo = $userservice->getObjectUserByUsername($user);
        if ($userinfo == null) {
            $tplVars['error'] = sprintf(T_('User with username %s was not found'), $user);
            $templateservice->loadTemplate('error.404.tpl', $tplVars);
            exit();
        } else {
            $userid =& $userinfo->getId();
        }
    }
} else {
    $tplVars['error'] = T_('Username was not specified');
    $templateservice->loadTemplate('error.404.tpl', $tplVars);
    exit();
}

if ($userservice->isLoggedOn() && $user == $currentUser->getUsername()) {
    $title = T_('My Profile');
} else {
    $title = T_('Profile') .': '. $user;
}
$tplVars['pagetitle'] = $title;
$tplVars['subtitle'] = $title;

$tplVars['user'] = $user;
$tplVars['userid'] = $userid;

if (POST_SUBMITTED!='' && $currentUser->getId() == $userid) {
    $error = false;
    $detPass = trim(POST_PASS);
    $detPassConf = trim(POST_PASSCONF);
    $detName = trim(POST_NAME);
    $detMail = trim(POST_MAIL);
    $detPage = trim(POST_PAGE);
    $detDesc = filter(POST_DESC);

    // manage token preventing from CSRF vulnaribilities
    //limit token lifetime, optionnal
    if ( SESSION_TOKEN == '' || time() - SESSION_TOKENSTAMP > 600 || SESSION_TOKEN != POST_TOKEN) {
        $error = true;
        $tplVars['error'] = T_('Invalid Token');
    }

    if ($detPass != $detPassConf) {
        $error = true;
        $tplVars['error'] = T_('Password and confirmation do not match.');
    }
    if ($detPass != "" && strlen($detPass) < 6) {
        $error = true;
        $tplVars['error'] = T_('Password must be at least 6 characters long.');
    }
    if (!$userservice->isValidEmail($detMail)) {
        $error = true;
        $tplVars['error'] = T_('E-mail address is not valid.');
    }
    if (!$error) {
        if (!$userservice->updateUser($userid, $detPass, $detName, $detMail, $detPage, $detDesc)) {
            $tplvars['error'] = T_('An error occurred while saving your changes.');
        } else {
            $tplVars['msg'] = T_('Changes saved.');
        }
    }
    $userinfo = $userservice->getObjectUserByUsername($user);
}

if (!$userservice->isLoggedOn() || $currentUser->getId() != $userid) {
    $templatename = 'profile.tpl.php';
} else {
    //Token Init
    $_SESSION['token'] = md5(uniqid(rand(), true));
    $_SESSION['token_stamp'] = time();

    $templatename = 'editprofile.tpl.php';
    $tplVars['formaction']  = createURL('profile', $user);
    $tplVars['token'] = $_SESSION['token'];
}

$tplVars['objectUser'] = $userinfo;
$templateservice->loadTemplate($templatename, $tplVars);
?>
