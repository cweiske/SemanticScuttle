<?php
if (!in_array('phar', stream_get_wrappers())
    || !class_exists('Phar', 0)
) {
    echo 'PHP Phar extension required';
    exit;
}


function mapUrls($path)
{
    if (substr($path, 0, 5) !== '/www/') {
        return false;
    }
    $arMap = array(
        '/www/' => '/www/index.php'
    );
    if (isset($arMap[$path])) {
        return $arMap[$path];
    }
    return $path;
}

Phar::interceptFileFuncs();
Phar::webPhar(
    null,
    'www/index.php',
    null,
    array(),
    'mapUrls'
);

__HALT_COMPILER(); ?>