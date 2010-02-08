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
isset($_POST['keeppass']) ? define('POST_KEEPPASS', $_POST['keeppass']): define('POST_KEEPPASS', '');
isset($_POST['submitted']) ? define('POST_SUBMITTED', $_POST['submitted']): define('POST_SUBMITTED', '');
isset($_POST['username']) ? define('POST_USERNAME', $_POST['username']): define('POST_USERNAME', '');
isset($_POST['password']) ? define('POST_PASSWORD', $_POST['password']): define('POST_PASSWORD', '');
isset($_POST['query']) ? define('POST_QUERY', $_POST['query']): define('POST_QUERY', '');

$keeppass = (POST_KEEPPASS=='yes')?true:false;

$login = false;
if (POST_SUBMITTED!='' && POST_USERNAME!='' && POST_PASSWORD!='') {
    $posteduser = trim(utf8_strtolower(POST_USERNAME));
    $login = $userservice->login($posteduser, POST_PASSWORD, $keeppass); 
    if ($login) {
        if (POST_QUERY)
            header('Location: '. createURL('bookmarks', $posteduser .'?'. POST_QUERY));
        else
            header('Location: '. createURL('bookmarks', $posteduser));
    } else {
        $tplVars['error'] = T_('The details you have entered are incorrect. Please try again.');
    }
}
if (!$login) { 
    if ($userservice->isLoggedOn()) {
        $cUser = $userservice->getCurrentObjectUser();
        header('Location: '. createURL('bookmarks', strtolower($cUser->getUsername())));
    }

    $tplVars['subtitle']    = T_('Log In');
    $tplVars['formaction']  = createURL('login');
    $tplVars['querystring'] = filter($_SERVER['QUERY_STRING']);
    $templateservice->loadTemplate('login.tpl', $tplVars);
}
?>
