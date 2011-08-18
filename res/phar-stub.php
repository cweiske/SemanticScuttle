<?php
if (!in_array('phar', stream_get_wrappers())
    || !class_exists('Phar', 0)
) {
    echo 'PHP Phar extension required';
    exit;
}


function mapUrls($path)
{
    $arMap = array(
        '/'         => '/www/index.php',
        '/gsearch/' => '/www/gsearch/index.php',
    );
    if (isset($arMap[$path])) {
        return $arMap[$path];
    }
    return '/www' . $path;
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