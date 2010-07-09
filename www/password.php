<?php
/**
 * SemanticScuttle - your social bookmark manager.
 * User password reset form.
 *
 * PHP version 5.
 *
 * @category  Bookmarking
 * @package   SemanticScuttle
 * @author    Benjamin Huynh-Kim-Bang <mensonge@users.sourceforge.net>
 * @author    Christian Weiske <cweiske@cweiske.de>
 * @author    Eric Dane <ericdane@users.sourceforge.net>
 * @author    Marcus Campbell <marcus.campbell@gmail.com>
 * @license   GPL http://www.gnu.org/licenses/gpl.html
 * @link      http://sourceforge.net/projects/semanticscuttle
 */
require_once 'www-header.php';

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
    array('action' => createURL('password')),
    true
);

$user = $form->addElement(
    'text', 'username',
    array(
        'size'    => 20,
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
    'callback',
    T_('No matches found for that username.'),
    array($userservice, 'existsUserWithUsername')
);
$form->addRule(
    'callback',
    T_('No matches found for that combination of username and <abbr title="electronic mail">e-mail</abbr> address.'),
    'checkUserEmailCombination'
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
    array('value' => T_('Generate Password'))
);

/**
 * Checks if the user and email combination exists in the database.
 *
 * @param array $arValues Key-value array of form values
 *
 * @return boolean True if it exists, false if not
 */
function checkUserEmailCombination($arValues)
{
    //FIXME: remove this once HTML_QuickForm2 calls form rules
    // only after element rules match
    // http://pear.php.net/bugs/17576
    if (trim($arValues['username']) == ''
        || trim($arValues['email']) == ''
    ) {
        return false;
    }

    $userservice = SemanticScuttle_Service_Factory::get('User');
    return $userservice->userEmailCombinationValid(
        $arValues['username'], $arValues['email']
    );
}



$tplVars['error'] = '';
if ($form->validate()) {
    $arValues = $form->getValue();
    $arUser   = $userservice->getUserByUsername($arValues['username']);
    $password = $userservice->generatePassword($arUser['uId']);
    if ($password === false) {
        $tplVars['error'] = T_('There was an error while generating your new password. Please try again.');
    } else {
        //change password and send email out
        $message = T_('Your new password is:')
            . "\n" . $password . "\n\n"
            . T_('To keep your bookmarks secure, you should change this password in your profile the next time you log in.');
        $message = wordwrap($message, 70);
        $headers = 'From: '. $adminemail;
        $mail    = mail(
            $arValues['email'],
            sprintf(T_('%s Account Information'), $sitename),
            $message
        );
        $tplVars['msg'] = sprintf(
            T_('New password generated and sent to %s'),
            $arValues['email']
        );
        $captcha->clearCaptchaSession();
    }
} else {
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
    //fscking form error is not in form|errors
    $tplVars['error']   .= implode(
        '<br/>',
        array_unique(
            array_merge(
                $tplVars['form']['errors'],
                array($form->getError())
            )
        )
    );
}

$tplVars['loadjs']   = true;
$tplVars['subtitle'] = T_('Forgotten Password');
$templateservice->loadTemplate('password.tpl', $tplVars);
?>
