<?php

class SemanticScuttle_EnvironmentTest extends PHPUnit_Framework_TestCase
{
    public function testServerPathInfoModPhpNoPath()
    {
        $_SERVER = array (
            'HTTP_USER_AGENT' => 'Opera/9.80 (X11; Linux x86_64) Presto/2.12.388 Version/12.16',
            'HTTP_HOST' => 'bm.bogo',
            'HTTP_ACCEPT' => 'text/html, application/xml;q=0.9, applicaton/xhtml+xml, image/png, image/webp, image/jpeg, image/gif, image/x-xbitmap, */*;q=0.1',
            'HTTP_ACCEPT_LANGUAGE' => 'en,de-DE;q=0.9,de;q=0.8',
            'HTTP_ACCEPT_ENCODING' => 'gzip, deflate',
            'HTTP_CONNECTION' => 'Keep-Alive',
            'HTTP_DNT' => '1',
            'PATH' => '/usr/local/bin:/usr/bin:/bin',
            'SERVER_SIGNATURE' => '<address>Apache/2.2.22 (Ubuntu) Server at bm.bogo Port 80</address>',
            'SERVER_SOFTWARE' => 'Apache/2.2.22 (Ubuntu)',
            'SERVER_NAME' => 'bm.bogo',
            'SERVER_ADDR' => '127.0.0.1',
            'SERVER_PORT' => '80',
            'REMOTE_ADDR' => '127.0.0.1',
            'DOCUMENT_ROOT' => '/var/www',
            'SERVER_ADMIN' => '[no address given]',
            'SCRIPT_FILENAME' => '/home/cweiske/Dev/html/hosts/bm.bogo/test.php',
            'REMOTE_PORT' => '38545',
            'GATEWAY_INTERFACE' => 'CGI/1.1',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'REQUEST_METHOD' => 'GET',
            'QUERY_STRING' => '',
            'REQUEST_URI' => '/test.php',
            'SCRIPT_NAME' => '/test.php',
            'PHP_SELF' => '/test.php',
            'REQUEST_TIME_FLOAT' => 1377024570.296,
            'REQUEST_TIME' => 1377024570,
        );
        $this->assertEquals(
            '', SemanticScuttle_Environment::getServerPathInfo()
        );
    }

    public function testServerPathInfoModPhp()
    {
        $_SERVER = array(
            'HTTP_USER_AGENT' => 'Opera/9.80 (X11; Linux x86_64; U; de) Presto/2.9.168 Version/11.50',
            'HTTP_HOST' => 'bm-cgi.bogo',
            'HTTP_ACCEPT' => 'text/html, application/xml;q=0.9, application/xhtml+xml, image/png, image/webp, image/jpeg, image/gif, image/x-xbitmap, */*;q=0.1',
            'HTTP_ACCEPT_LANGUAGE' => 'de-DE,de;q=0.9,en;q=0.8',
            'HTTP_ACCEPT_ENCODING' => 'gzip, deflate',
            'HTTP_COOKIE' => 'PHPSESSID=ga446jhs0e09hkt60u9bsmp0n0',
            'HTTP_CACHE_CONTROL' => 'no-cache',
            'HTTP_CONNECTION' => 'Keep-Alive',
            'PATH' => '/usr/local/bin:/usr/bin:/bin',
  'SERVER_SIGNATURE' => '<address>Apache/2.2.17 (Ubuntu) Server at bm-cgi.bogo Port 80</address>',
            'SERVER_SOFTWARE' => 'Apache/2.2.17 (Ubuntu)',
            'SERVER_NAME' => 'bm-cgi.bogo',
            'SERVER_ADDR' => '127.0.0.1',
            'SERVER_PORT' => '80',
            'REMOTE_ADDR' => '127.0.0.1',
            'DOCUMENT_ROOT' => '/etc/apache2/htdocs',
            'SERVER_ADMIN' => '[no address given]',
            'SCRIPT_FILENAME' => '/home/cweiske/Dev/html/hosts/bm-cgi.bogo/profile.php',
            'REMOTE_PORT' => '45349',
            'GATEWAY_INTERFACE' => 'CGI/1.1',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'REQUEST_METHOD' => 'GET',
            'QUERY_STRING' => '',
            'REQUEST_URI' => '/profile.php/dummy',
            'SCRIPT_NAME' => '/profile.php',
            'PATH_INFO' => '/dummy',
            'PATH_TRANSLATED' => '/home/cweiske/Dev/html/hosts/bm-cgi.bogo/dummy',
            'PHP_SELF' => '/profile.php/dummy',
            'REQUEST_TIME' => 1311422546,
        );
        $this->assertEquals(
            '/dummy', SemanticScuttle_Environment::getServerPathInfo()
        );
    }


    public function testServerPathInfoFastCgi()
    {
        $_SERVER = array(
            'PHP_FCGI_MAX_REQUESTS' => '5000',
            'PHPRC' => '/etc/php5/cgi/5.3.6/',
            'PHP_FCGI_CHILDREN' => '3',
            'PWD' => '/var/www/cgi-bin',
            'FCGI_ROLE' => 'RESPONDER',
            'REDIRECT_HANDLER' => 'php-cgi',
            'REDIRECT_STATUS' => '200',
            'HTTP_USER_AGENT' => 'Opera/9.80 (X11; Linux x86_64; U; de) Presto/2.9.168 Version/11.50',
            'HTTP_HOST' => 'bm-cgi.bogo',
            'HTTP_ACCEPT' => 'text/html, application/xml;q=0.9, application/xhtml+xml, image/png, image/webp, image/jpeg, image/gif, image/x-xbitmap, */*;q=0.1',
            'HTTP_ACCEPT_LANGUAGE' => 'de-DE,de;q=0.9,en;q=0.8',
            'HTTP_ACCEPT_ENCODING' => 'gzip, deflate',
            'HTTP_COOKIE' => 'PHPSESSID=ga446jhs0e09hkt60u9bsmp0n0',
            'HTTP_CONNECTION' => 'Keep-Alive',
            'PATH' => '/usr/local/bin:/usr/bin:/bin',
  'SERVER_SIGNATURE' => '<address>Apache/2.2.17 (Ubuntu) Server at bm-cgi.bogo Port 80</address>',
            'SERVER_SOFTWARE' => 'Apache/2.2.17 (Ubuntu)',
            'SERVER_NAME' => 'bm-cgi.bogo',
            'SERVER_ADDR' => '127.0.0.1',
            'SERVER_PORT' => '80',
            'REMOTE_ADDR' => '127.0.0.1',
            'DOCUMENT_ROOT' => '/etc/apache2/htdocs',
            'SERVER_ADMIN' => '[no address given]',
            'SCRIPT_FILENAME' => '/home/cweiske/Dev/html/hosts/bm-cgi.bogo/profile.php',
            'REMOTE_PORT' => '45342',
            'REDIRECT_URL' => '/profile.php/dummy',
            'GATEWAY_INTERFACE' => 'CGI/1.1',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'REQUEST_METHOD' => 'GET',
            'QUERY_STRING' => '',
            'REQUEST_URI' => '/profile.php/dummy',
            'SCRIPT_NAME' => '/profile.php',
            'PATH_INFO' => '/dummy',
            'PATH_TRANSLATED' => '/etc/apache2/htdocs/dummy',
            'ORIG_PATH_INFO' => '/profile.php/dummy',
            'ORIG_SCRIPT_NAME' => '/cgi-bin-php/php-cgi-5.3.6',
            'ORIG_SCRIPT_FILENAME' => '/var/www/cgi-bin/php-cgi-5.3.6',
            'ORIG_PATH_TRANSLATED' => '/home/cweiske/Dev/html/hosts/bm-cgi.bogo/profile.php/dummy',
            'PHP_SELF' => '/profile.php/dummy',
            'REQUEST_TIME' => 1311422521,
        );
        $this->assertEquals(
            '/dummy', SemanticScuttle_Environment::getServerPathInfo()
        );
    }

    public function testServerPathInfo1and1NoPath()
    {
        $_SERVER = array(
            'REDIRECT_SCRIPT_URL' => '/dummy.php',
            'REDIRECT_SCRIPT_URI' => 'http://www.example.org/dummy.php',
            'REDIRECT_DOCUMENT_ROOT' => '/kunden/homepages/44/dexample/htdocs/example/www',
            'REDIRECT_HANDLER' => 'x-mapp-php6',
            'REDIRECT_STATUS' => '200',
            'DBENTRY_HOST' => 'example.org',
            'DBENTRY' => '/kunden/homepages/44/dexample/htdocs/example/www:d0000#CPU 6 #MEM 10240 #CGI 18 #NPROC 12 #TAID 46322755 #WERB 0 #LANG 2 #STAT 1',
            'SCRIPT_URL' => '/dummy.php',
            'SCRIPT_URI' => 'http://www.example.org/dummy.php',
            'HTTP_USER_AGENT' => 'Opera/9.80 (X11; Linux x86_64) Presto/2.12.388 Version/12.16',
            'HTTP_HOST' => 'www.example.org',
            'HTTP_ACCEPT' => 'text/html, application/xml;q=0.9, application/xhtml+xml, image/png, image/webp, image/jpeg, image/gif, image/x-xbitmap, */*;q=0.1',
            'HTTP_ACCEPT_LANGUAGE' => 'en,de-DE;q=0.9,de;q=0.8',
            'HTTP_ACCEPT_ENCODING' => 'gzip, deflate',
            'HTTP_COOKIE' => 'PHPSESSID=8c7853d7f639b3c6d24c224cf7d4cb1c',
            'HTTP_CONNECTION' => 'Keep-Alive',
            'HTTP_DNT' => '1',
            'PATH' => '/bin:/usr/bin',
            'SERVER_SIGNATURE' => '',
            'SERVER_SOFTWARE' => 'Apache',
            'SERVER_NAME' => 'example.org',
            'SERVER_ADDR' => '127.0.0.1',
            'SERVER_PORT' => '80',
            'REMOTE_ADDR' => '127.0.0.1',
            'DOCUMENT_ROOT' => '/kunden/homepages/44/dexample/htdocs/example/www',
            'SERVER_ADMIN' => 'webmaster@example.org',
            'SCRIPT_FILENAME' => '/kunden/homepages/44/dexample/htdocs/example/www/dummy.php',
            'REMOTE_PORT' => '35368',
            'REDIRECT_URL' => '/dummy.php',
            'GATEWAY_INTERFACE' => 'CGI/1.1',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'REQUEST_METHOD' => 'GET',
            'QUERY_STRING' => '',
            'REQUEST_URI' => '/dummy.php',
            'SCRIPT_NAME' => '/dummy.php',
            'STATUS' => '200',
            'ORIG_PATH_INFO' => '/dummy.php',
            'ORIG_PATH_TRANSLATED' => '/kunden/homepages/44/dexample/htdocs/example/www/dummy.php',
            'PHP_SELF' => '/dummy.php',
            'REQUEST_TIME_FLOAT' => 1377022156.0101,
            'REQUEST_TIME' => 1377022156,
            'argv' => array(),
            'argc' => 0,
        );
        $this->assertEquals(
            '', SemanticScuttle_Environment::getServerPathInfo()
        );
    }

    public function testServerPathInfo1and1WithPath()
    {
        $_SERVER = array(
            'REDIRECT_SCRIPT_URL' => '/dummy.php/dummy/foo',
            'REDIRECT_SCRIPT_URI' => 'http://www.example.org/dummy.php/dummy/foo',
            'REDIRECT_DOCUMENT_ROOT' => '/kunden/homepages/44/dexample/htdocs/example/www',
            'REDIRECT_HANDLER' => 'x-mapp-php6',
            'REDIRECT_STATUS' => '200',
            'DBENTRY_HOST' => 'example.org',
            'DBENTRY' => '/kunden/homepages/44/dexample/htdocs/example/www:d0000#CPU 6 #MEM 10240 #CGI 18 #NPROC 12 #TAID 46322755 #WERB 0 #LANG 2 #STAT 1',
            'SCRIPT_URL' => '/dummy.php/dummy/foo',
            'SCRIPT_URI' => 'http://www.example.org/dummy.php/dummy/foo',
            'HTTP_USER_AGENT' => 'Opera/9.80 (X11; Linux x86_64) Presto/2.12.388 Version/12.16',
            'HTTP_HOST' => 'www.example.org',
            'HTTP_ACCEPT' => 'text/html, application/xml;q=0.9, application/xhtml+xml, image/png, image/webp, image/jpeg, image/gif, image/x-xbitmap, */*;q=0.1',
            'HTTP_ACCEPT_LANGUAGE' => 'en,de-DE;q=0.9,de;q=0.8',
            'HTTP_ACCEPT_ENCODING' => 'gzip, deflate',
            'HTTP_COOKIE' => 'PHPSESSID=8c7853d7f639b3c6d24c224cf7d4cb1c',
            'HTTP_CONNECTION' => 'Keep-Alive',
            'HTTP_DNT' => '1',
            'PATH' => '/bin:/usr/bin',
            'SERVER_SIGNATURE' => '',
            'SERVER_SOFTWARE' => 'Apache',
            'SERVER_NAME' => 'example.org',
            'SERVER_ADDR' => '127.0.0.1',
            'SERVER_PORT' => '80',
            'REMOTE_ADDR' => '127.0.0.1',
            'DOCUMENT_ROOT' => '/kunden/homepages/44/dexample/htdocs/example/www',
            'SERVER_ADMIN' => 'webmaster@example.org',
            'SCRIPT_FILENAME' => '/kunden/homepages/44/dexample/htdocs/example/www/dummy.php',
            'REMOTE_PORT' => '35857',
            'REDIRECT_URL' => '/dummy.php/dummy/foo',
            'GATEWAY_INTERFACE' => 'CGI/1.1',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'REQUEST_METHOD' => 'GET',
            'QUERY_STRING' => '',
            'REQUEST_URI' => '/dummy.php/dummy/foo',
            'SCRIPT_NAME' => '/dummy.php',
            'STATUS' => '200',
            'ORIG_PATH_INFO' => '/dummy/foo',
            'ORIG_PATH_TRANSLATED' => '/kunden/homepages/44/dexample/htdocs/example/www/dummy.php',
            'PHP_SELF' => '/dummy.php',
            'REQUEST_TIME_FLOAT' => 1377024137.8098,
            'REQUEST_TIME' => 1377024137,
            'argv' => array(),
            'argc' => 0,
        );
        $this->assertEquals(
            '/dummy/foo', SemanticScuttle_Environment::getServerPathInfo()
        );
    }
}

?>