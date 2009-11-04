<?php
/**
 * Compile a translation file from .po to .mo
 */
chdir(dirname(dirname(__FILE__)));

if ($argc < 2) {
    die("pass language name to update, i.e 'de_DE'\n");
}
$lang = $argv[1];

$langdir = 'data/locales/' . $lang;
if (!is_dir($langdir)) {
    die('There is no language directory: ' . $langdir . "\n");
}


passthru(
    'msgfmt --statistics'
    . ' ' . $langdir . '/LC_MESSAGES/messages.po'
    . ' -o ' . $langdir . '/LC_MESSAGES/messages.mo'
);

?>