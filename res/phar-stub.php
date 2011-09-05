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
    $parts = explode('/', $path);
    $partPos = 1;
    if (in_array($parts[$partPos], array('js', 'player', 'themes'))) {
        return '/www' . $path;
    }
    if (in_array($parts[$partPos], array('ajax', 'api', 'gsearch'))) {
        $partPos = 2;
    }
    $pos = strrpos($parts[$partPos], '.');
    if ($pos === false) {
        $parts[$partPos] .= '.php';
        $_SERVER['PATH_INFO'] = '/' . implode(
            '/', array_slice($parts, $partPos + 1)
        );
    }
    return '/www' . implode('/', $parts);
}

Phar::webPhar(
    null,
    'www/index.php',
    null,
    array(),
    'mapUrls'
);

__HALT_COMPILER(); ?>