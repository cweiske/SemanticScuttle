<?php
/***************************************************************************
Copyright (C) 2004 - 2006 Marcus Campbell
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

require_once('header.inc.php');

/* Service creation: only useful services are created */
$userservice =& ServiceFactory::getServiceInstance('UserService');
$templateservice =& ServiceFactory::getServiceInstance('TemplateService');

/* Managing all possible inputs */
isset($_POST['submitted']) ? define('POST_SUBMITTED', $_POST['submitted']): define('POST_SUBMITTED', '');
isset($_POST['username']) ? define('POST_USERNAME', $_POST['username']): define('POST_USERNAME', '');
isset($_POST['password']) ? define('POST_PASS', $_POST['password']): define('POST_PASS', '');
isset($_POST['email']) ? define('POST_MAIL', $_POST['email']): define('POST_MAIL', '');
isset($_POST['antispamAnswer']) ? define('POST_ANTISPAMANSWER', $_POST['antispamAnswer']): define('POST_ANTISPAMANSWER', '');


$tplVars = array();

if (POST_SUBMITTED != '') {
    $posteduser = trim(utf8_strtolower(POST_USERNAME));

    // Check if form is incomplete
    if (!($posteduser) || POST_PASS == '' || POST_MAIL == '') {    	
        $tplVars['error'] = T_('You <em>must</em> enter a username, password and e-mail address.');

    // Check if username is reserved
    } elseif ($userservice->isReserved($posteduser)) {
        $tplVars['error'] = T_('This username has been reserved, please make another choice.');

    // Check if username already exists
    } elseif ($userservice->getUserByUsername($posteduser)) {
        $tplVars['error'] = T_('This username already exists, please make another choice.');
        
    // Check if username is valid (length, authorized characters)
    } elseif (!$userservice->isValidUsername($posteduser)) {
        $tplVars['error'] = T_('This username is not valid (too long, forbidden characters...), please make another choice.');        
    
    // Check if e-mail address is valid
    } elseif (!$userservice->isValidEmail(POST_MAIL)) {
        $tplVars['error'] = T_('E-mail address is not valid. Please try again.');

    // Check if antispam answer is valid
    } elseif (strcmp(POST_ANTISPAMANSWER, $GLOBALS['antispamAnswer']) != 0) {
        $tplVars['error'] = T_('Antispam answer is not valid. Please try again.');

    // Register details
    } elseif ($userservice->addUser($posteduser, POST_PASS, POST_MAIL)) {
        // Log in with new username
        $login = $userservice->login($posteduser, POST_PASS);
        if ($login) {
            header('Location: '. createURL('bookmarks', $posteduser));
        }
        $tplVars['msg'] = T_('You have successfully registered. Enjoy!');
    } else {
        $tplVars['error'] = T_('Registration failed. Please try again.');
    }
}

$tplVars['antispamQuestion'] = $GLOBALS['antispamQuestion'];
$tplVars['loadjs']      = true;
$tplVars['subtitle']    = T_('Register');
$tplVars['formaction']  = createURL('register');
$templateservice->loadTemplate('register.tpl', $tplVars);
?>
