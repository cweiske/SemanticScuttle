<?php
/**
 * SemanticScuttle - your social bookmark manager.
 * User login form.
 *
 * PHP version 5.
 *
 * @category  Bookmarking
 * @package   SemanticScuttle
 * @author    Benjamin Huynh-Kim-Bang <mensonge@users.sourceforge.net>
 * @author    Christian Weiske <cweiske@cweiske.de>
 * @author    Eric Dane <ericdane@users.sourceforge.net>
 * @license   GPL http://www.gnu.org/licenses/gpl.html
 * @link      http://sourceforge.net/projects/semanticscuttle
 */
require_once 'www-header.php';

if ($userservice->isLoggedOn()) {
    //no need to log in when the user is already logged in
    $user = $userservice->getCurrentUser();
    header(
        'Location: '
        . createURL('bookmarks', $user['username'])
    );
    exit();
}

require_once 'HTML/QuickForm2.php';
require_once 'SemanticScuttle/QuickForm2/Renderer/CoolArray.php';

//do not append '-0' to IDs
HTML_Common2::setOption('id_force_append_index', false);

$login = new HTML_QuickForm2(
    'login', 'post',
    array('action' => createURL('login')),
    true
);
$login->addElement(
    'hidden', 'querystring',
    array(
        'value' => $_SERVER['QUERY_STRING']
    )
);

$user = $login->addElement(
    'text', 'username',
    array(
        'size'    => 20,
        'class'   => 'required'
    )
)->setLabel(T_('Username'));
$user->addRule(
    'required',
    T_('Please enter your username')
);
$user->addRule(
    'callback',
    T_('This username is not valid (too short, too long, forbidden characters...), please make another choice.'),
    array($userservice, 'isValidUsername')
);

$login->addElement(
    'password', 'password',
    array(
        'size'  => 20,
        'class' => 'required'
    )
)
->setLabel(T_('Password'))
->addRule(
    'required',
    T_('Please enter your password')
);

$login->addElement(
    'checkbox', 'keeploggedin'
)->setLabel(T_('Don\'t ask for my password for 2 weeks'));

$login->addElement(
    'submit', 'submit',
    array('value' => T_('Log In'))
);


$tplVars['error'] = '';
if ($login->validate()) {
    $arValues = $login->getValue();
    if (!isset($arValues['keeploggedin'])) {
        $arValues['keeploggedin'] = false;
    }
    $bLoginOk = $userservice->login(
        $arValues['username'],
        $arValues['password'],
        (bool)$arValues['keeploggedin']
    );
    if ($bLoginOk) {
        if ($arValues['querystring'] != '') {
            //append old query string
            header(
                'Location: '
                . createURL('bookmarks', $arValues['username'])
                . '?' . $arValues['querystring']
            );
        } else {
            header(
                'Location: '
                . createURL('bookmarks', $arValues['username'])
            );
        }
        exit();
    }
    $tplVars['error'] = T_('The details you have entered are incorrect. Please try again.');
}


HTML_QuickForm2_Renderer::register(
    'coolarray',
    'SemanticScuttle_QuickForm2_Renderer_CoolArray'
);
//$renderer = HTML_QuickForm2_Renderer::factory('coolarray')
$renderer = new SemanticScuttle_QuickForm2_Renderer_CoolArray();
$renderer->setOption(
    array(
        'group_hiddens' => true,
        'group_errors'  => true
    )
);

$tplVars['form']     = $login->render($renderer);
$tplVars['loadjs']   = true;
$tplVars['subtitle'] = T_('Register');
$tplVars['error']   .= implode(
    '<br/>', array_unique($tplVars['form']['errors'])
);
$templateservice->loadTemplate('login.tpl', $tplVars);

?>
