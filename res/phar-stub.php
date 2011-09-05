<?php
if (!in_array('phar', stream_get_wrappers())
    || !class_exists('Phar', 0)
) {
    echo 'PHP Phar extension required';
    exit;
}

Phar::interceptFileFuncs();

if (php_sapi_name() == 'cli') {
    require_once dirname(__FILE__) . '/../src/SemanticScuttle/Phar/Cli.php';
    $cli = new SemanticScuttle_Phar_Cli();
    $cli->run();
    exit;
}

function mapUrls($path)
{
    $arMap = array(
        ''          => '/www/index.php',
        '/'         => '/www/index.php',
        '/gsearch/' => '/www/gsearch/index.php',
    );
    if (isset($arMap[$path])) {
        return $arMap[$path];
    }
    $pos = strrpos($path, '.');
    if ($pos === false || strlen($path) - $pos > 5) {
        //clean url
        $path .= '.php';
    }
    return '/www' . $path;
}

Phar::webPhar(
    null,
    'www/index.php',
    null,
    array(),
    'mapUrls'
);

__HALT_COMPILER(); ?>