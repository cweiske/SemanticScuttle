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

require_once 'www-header.php';

if (!$GLOBALS['enableRegistration']) {
    header('HTTP/1.0 501 Not implemented');
    echo 'Registration is disabled';
    exit(1);
}

require_once 'HTML/QuickForm2.php';
require_once 'HTML/QuickForm2/Renderer.php';
require_once 'SemanticScuttle/QuickForm2/Element/BackgroundText.php';
require_once 'SemanticScuttle/QuickForm2/Rule/ICallback.php';

HTML_QuickForm2_Factory::registerElement(
    'backgroundtext', 
    'SemanticScuttle_QuickForm2_Element_BackgroundText'
);
HTML_QuickForm2_Factory::registerRule(
    'icallback',
    'SemanticScuttle_QuickForm2_Rule_ICallback'
);

$form = new HTML_QuickForm2(
    'registration', 'post',
    array('action' => createURL('register')),
    true
);

$user = $form->addElement(
    'text', 'username',
    array(
        'id'      => 'username',
        'size'    => 20,
        'onkeyup' => 'isAvailable(this, "")',
        'class'   => 'required'
    )
)->setLabel(T_('Username'));
$user->addRule(
    'required',
    T_('You <em>must</em> enter a username, password and e-mail address.')
);
$user->addRule(
    'callback',
    T_('This username is not valid (too short, too long, forbidden characters...), please make another choice.'),
    array($userservice, 'isValidUsername')
);
$user->addRule(
    'icallback',
    T_('This username has been reserved, please make another choice.'),
    array($userservice, 'isReserved')
);
$user->addRule(
    'icallback',
    T_('This username already exists, please make another choice.'),
    array($userservice, 'existsUserWithUsername')
);

$form->addElement(
    'password', 'password',
    array(
        'id'    => 'password',
        'size'  => 20,
        'class' => 'required'
    )
)
->setLabel(T_('Password'))
->addRule(
    'required',
    T_('You <em>must</em> enter a username, password and e-mail address.')
);

$email = $form->addElement(
    'text', 'email',
    array(
        'id'    => 'email',
        'size'  => 40,
        'class' => 'required'
    )
)->setLabel(T_('E-mail'));
$email->addRule(
    'required',
    T_('You <em>must</em> enter a username, password and e-mail address.')
);
$email->addRule(
    'callback',
    T_('E-mail address is not valid. Please try again.'),
    array($userservice, 'isValidEmail')
);

$form->addElement(
    'backgroundtext', 'antispamAnswer',
    array(
        'id'   => 'antispamAnswer',
        'size' => 40
    )
)
->setLabel(T_('Antispam question'))
->setBackgroundText($GLOBALS['antispamQuestion'])
->setBackgroundClass('inacttext')
->addRule(
    'callback',
    T_('Antispam answer is not valid. Please try again.'),
    'verifyAntiSpamAnswer'
);
//FIXME: custom rule or captcha element

$form->addElement(
    'submit', 'submitted', array('id' => 'submit')
)
->setLabel(T_('Register'));

function verifyAntiSpamAnswer($userAnswer)
{
    return strcasecmp(
        str_replace(' ', '', $userAnswer),
        str_replace(' ', '', $GLOBALS['antispamAnswer'])
    ) == 0;
}

$tplVars['error'] = '';
if ($form->validate()) {
    $arValues = $form->getValue();
    //FIXME: how to fetch single values?
    $bOk = $userservice->addUser(
        $arValues['username'], $arValues['password'], $arValues['email']
    );
    if ($bOk) {
        header('Location: '. createURL('bookmarks', $arValues['username']));
        exit();
    }
    $tplVars['error'] .= T_('Registration failed. Please try again.');
}

HTML_QuickForm2_Renderer::register(
    'coolarray',
    'SemanticScuttle_QuickForm2_Renderer_CoolArray'
);
require_once 'SemanticScuttle/QuickForm2/Renderer/CoolArray.php';
//$renderer = HTML_QuickForm2_Renderer::factory('coolarray')
$renderer = new SemanticScuttle_QuickForm2_Renderer_CoolArray();
$renderer->setOption(
    array(
        'group_hiddens' => true,
        'group_errors'  => true
    )
);

$tplVars['form']     = $form->render($renderer);
$tplVars['loadjs']   = true;
$tplVars['subtitle'] = T_('Register');
$tplVars['error']   .= implode(
    '<br/>', array_unique($tplVars['form']['errors'])
);
$templateservice->loadTemplate('register.tpl', $tplVars);

exit();






/* Managing all possible inputs */
isset($_POST['submitted']) ? define('POST_SUBMITTED', $_POST['submitted']): define('POST_SUBMITTED', '');
isset($_POST['username']) ? define('POST_USERNAME', $_POST['username']): define('POST_USERNAME', '');
isset($_POST['password']) ? define('POST_PASS', $_POST['password']): define('POST_PASS', '');
isset($_POST['email']) ? define('POST_MAIL', $_POST['email']): define('POST_MAIL', '');
isset($_POST['antispamAnswer']) ? define('POST_ANTISPAMANSWER', $_POST['antispamAnswer']): define('POST_ANTISPAMANSWER', '');


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
        $tplVars['error'] = T_('This username is not valid (too short, too long, forbidden characters...), please make another choice.');        
    
    // Check if e-mail address is valid
    } elseif (!$userservice->isValidEmail(POST_MAIL)) {
        $tplVars['error'] = T_('E-mail address is not valid. Please try again.');

    // Check if antispam answer is valid (doesn't take into account spaces and uppercase)
    } elseif (strcasecmp(str_replace(' ', '', POST_ANTISPAMANSWER), str_replace(' ', '', $GLOBALS['antispamAnswer'])) != 0) {
        $tplVars['error'] = T_('Antispam answer is not valid. Please try again.');

    // Register details
    } elseif ($userservice->addUser($posteduser, POST_PASS, POST_MAIL) !== false) {
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
