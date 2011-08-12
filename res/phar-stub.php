<?php
if (!in_array('phar', stream_get_wrappers())
    || !class_exists('Phar', 0)
) {
    echo 'PHP Phar extension required';
    exit;
}

//disallow access to everything except /www/
$file = basename(__FILE__);
$pos = strpos($_SERVER['REQUEST_URI'], $file);
$following = substr($_SERVER['REQUEST_URI'], $pos + strlen($file), 5);

if ($following != '/www/'
    && $following !== false
    && $following != '/'
) {
    header('403 Forbidden');
    echo <<<HTM
<html>
 <head>
  <title>Forbidden</title>
 </head>
 <body>
  <h1>403 - Forbidden</h1>
 </body>
</html>
HTM;
    exit;
}

Phar::interceptFileFuncs();
Phar::webPhar(
    null,
    'www/index.php'
);

__HALT_COMPILER(); ?>