<?php
/**
 * SemanticScuttle - your social bookmark manager.
 * New user registration form.
 *
 * PHP version 5.
 *
 * @category  Bookmarking
 * @package   SemanticScuttle
 * @author    Benjamin Huynh-Kim-Bang <mensonge@users.sourceforge.net>
 * @author    Christian Weiske <cweiske@cweiske.de>
 * @author    Eric Dane <ericdane@users.sourceforge.net>
 * @author    Marcus Campbell <marcus.campbell@gmail.com>
 * @copyright 2004-2006 Marcus Campbell
 * @license   GPL http://www.gnu.org/licenses/gpl.html
 * @link      http://sourceforge.net/projects/semanticscuttle
 */
require_once 'www-header.php';

if (!$GLOBALS['enableRegistration']) {
    header('HTTP/1.0 501 Not implemented');
    echo 'Registration is disabled';
    exit(1);
}

require_once 'HTML/QuickForm2.php';
require_once 'SemanticScuttle/QuickForm2/Renderer/CoolArray.php';
require_once 'HTML/QuickForm2/Element/NumeralCaptcha.php';

//we register a strange name here so we can change the class
// itself easily
HTML_QuickForm2_Factory::registerElement(
    'sc-captcha',
    'HTML_QuickForm2_Element_NumeralCaptcha'
);

//do not append '-0' to IDs
HTML_Common2::setOption('id_force_append_index', false);

$form = new HTML_QuickForm2(
    'registration', 'post',
    array('action' => createURL('register')),
    true
);

$user = $form->addElement(
    'text', 'username',
    array(
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
    'notcallback',
    T_('This username has been reserved, please make another choice.'),
    array($userservice, 'isReserved')
);
$user->addRule(
    'notcallback',
    T_('This username already exists, please make another choice.'),
    array($userservice, 'existsUserWithUsername')
);

$form->addElement(
    'password', 'password',
    array(
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

$captcha = $form->addElement(
    'sc-captcha', 'captcha',
    array(
        'size' => 40
    ),
    array(
        'captchaSolutionWrong'
            => T_('Antispam answer is not valid. Please try again.')
    )
)
->setLabel(T_('Antispam question'));

$form->addElement(
    'submit', 'submit',
    array('value' => T_('Register'))
);


$tplVars['error'] = '';
if ($form->validate()) {
    $arValues = $form->getValue();

    $bOk = $userservice->addUser(
        $arValues['username'], $arValues['password'], $arValues['email']
    );
    if ($bOk) {
        $captcha->clearCaptchaSession();
        header('Location: '. createURL('bookmarks', $arValues['username']));
        exit();
    }
    $tplVars['error'] .= T_('Registration failed. Please try again.');
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

$tplVars['form']     = $form->render($renderer);
$tplVars['loadjs']   = true;
$tplVars['subtitle'] = T_('Register');
$tplVars['error']   .= implode(
    '<br/>', array_unique($tplVars['form']['errors'])
);
$templateservice->loadTemplate('register.tpl', $tplVars);
?>
