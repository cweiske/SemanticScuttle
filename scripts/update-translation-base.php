<?php
/**
 * Update gettext translation base file
 */
chdir(dirname(dirname(__FILE__)));

//do not include php-gettext or database layer
passthru(
    'xgettext -kT_ngettext:1,2 -kT_ -L PHP'
    . ' -o data/locales/messages.po'
    . ' src/SemanticScuttle/*.php'
    . ' src/SemanticScuttle/Model/*.php'
    . ' src/SemanticScuttle/Service/*.php'
    . ' data/templates/default/*.php'
    . ' www/*.php'
    . ' www/*/*.php'
);

?>