<?php
/**
 * SemanticScuttle - your social bookmark manager.
 *
 * PHP version 5.
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */

require_once 'HTTP/Request2.php';

/**
 * Base unittest class for web API tests.
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author Christian Weiske <cweiske@cweiske.de>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */
class TestBaseApi extends TestBase
{
    /**
     * Created from the configured host and the $urlPart.
     * Should be used as base for all generated URLs
     *
     * @var string
     */
    protected $url;

    /**
     * Part of the URL behind the configured host.
     * Needs to be overwritten in each derived test case class.
     *
     * @var string
     */
    protected $urlPart = null;

    /**
     * @var SemanticScuttle_Service_User
     */
    protected $us;

    /**
     * @var SemanticScuttle_Service_Bookmark
     */
    protected $bs;



    protected function setUp()
    {
        if ($GLOBALS['unittestUrl'] === null) {
            $this->markTestSkipped('Unittest URL not set in config');
        }
        if ($this->urlPart === null) {
            $this->assertTrue(false, 'Set the urlPart variable');
        }
        $this->url = $GLOBALS['unittestUrl'] . $this->urlPart;

        //clean up before test
        $configFile = $GLOBALS['datadir'] . '/config.testing-tmp.php';
        if (file_exists($configFile)) {
            unlink($configFile);
        }

        $this->us = SemanticScuttle_Service_Factory::get('User');
        $this->us->deleteAll();
        $this->bs = SemanticScuttle_Service_Factory::get('Bookmark');
        $this->bs->deleteAll();
        $this->b2t = SemanticScuttle_Service_Factory::get('Bookmark2Tag');
        $this->b2t->deleteAll();
    }



    /**
     * Creates and returns a HTTP GET request object.
     * Uses $this->url plus $urlSuffix as request URL.
     *
     * @param string $urlSuffix Suffix for the URL
     *
     * @return HTTP_Request2 HTTP request object
     */
    protected function getRequest($urlSuffix = null)
    {
        $url = $this->getTestUrl($urlSuffix);
        $req = new HTTP_Request2($url, HTTP_Request2::METHOD_GET);

        return $req;
    }

    /**
     * Creates an URL from $this->url plus $urlSuffix and an appended
     * unittestMode=1 parameter.
     *
     * @param string $urlSuffix Suffix for the URL
     *
     * @return string URL
     *
     * @uses $url
     */
    protected function getTestUrl($urlSuffix = null)
    {
        $url = $this->url . $urlSuffix;
        if (strpos($urlSuffix, '?') !== false) {
            $url .= '&unittestMode=1';
        } else {
            $url .= '?unittestMode=1';
        }
        return $url;
    }


    /**
     * Completes an URL that's missing the protocol.
     * Useful when re-using URLs extracted from HTML
     *
     * @param string $url Potentially partial URL
     *
     * @return string Full URL
     */
    protected function completeUrl($url)
    {
        if (substr($url, 0, 2) == '//') {
            $url = 'http:' . $url;
        }
        return $url;
    }



    /**
     * Creates a user and a HTTP GET request object and prepares
     * the request object with authentication details, so that
     * the user is logged in.
     *
     * Useful for HTTP API methods only, cannot be used with
     * "normal" HTML pages since they do not support HTTP auth.
     *
     * @param string $urlSuffix Suffix for the URL
     * @param mixed  $auth      If user authentication is needed (true/false)
     *                          or array with username and password
     *
     * @return array(HTTP_Request2, integer) HTTP request object and user id
     *
     * @uses getRequest()
     * @see getLoggedInRequest()
     */
    protected function getAuthRequest($urlSuffix = null, $auth = true)
    {
        $req = $this->getRequest($urlSuffix);
        if (is_array($auth)) {
            list($username, $password) = $auth;
        } else {
            $username = 'testuser';
            $password = 'testpassword';
        }
        $uid = $this->addUser($username, $password);
        $req->setAuth(
            $username, $password,
            HTTP_Request2::AUTH_BASIC
        );
        return array($req, $uid);
    }



    /**
     * Creates a user and a HTTP_Request2 object, does a normal login
     * and prepares the cookies for the HTTP GET request object so that
     * the user is seen as logged in when requesting any HTML page.
     *
     * Useful for testing HTML pages or ajax URLs.
     *
     * @param string  $urlSuffix  Suffix for the URL
     * @param mixed   $auth       If user authentication is needed (true/false)
     *                            or array with username and password
     * @param boolean $privateKey True if to add user with private key
     *
     * @return array(HTTP_Request2, integer) HTTP request object and user id
     *
     * @uses getRequest()
     */
    protected function getLoggedInRequest(
        $urlSuffix = null, $auth = true, $privateKey = null
    ) {
        if (is_array($auth)) {
            list($username, $password) = $auth;
        } else {
            $username = 'testuser';
            $password = 'testpassword';
        }
        $uid = $this->addUser($username, $password, $privateKey);

        $req = new HTTP_Request2(
            $GLOBALS['unittestUrl'] . '/login.php?unittestMode=1',
            HTTP_Request2::METHOD_POST
        );
        $cookies = $req->setCookieJar()->getCookieJar();
        $req->addPostParameter('username', $username);
        $req->addPostParameter('password', $password);
        $req->addPostParameter('submitted', 'Log In');
        $res = $req->send();

        //after login, we normally get redirected
        $this->assertEquals(302, $res->getStatus(), 'Login failure');

        $req = $this->getRequest($urlSuffix);
        $req->setCookieJar($cookies);

        return array($req, $uid);
    }



    /**
     * Verifies that the HTTP response has status code 200 and
     * content-type application/json; charset=utf-8
     *
     * @param HTTP_Request2_Response $res HTTP Response object
     *
     * @return void
     */
    protected function assertResponseJson200(HTTP_Request2_Response $res)
    {
        $this->assertEquals(200, $res->getStatus());
        $this->assertEquals(
            'application/json; charset=utf-8',
            $res->getHeader('content-type')
        );
    }



    /**
     * Writes a special unittest configuration file.
     * The unittest config file is read when a GET request with unittestMode=1
     * is sent, and the user allowed unittestmode in config.php.
     *
     * @param array $arConfig Array with config names as key and their value as
     *                        value
     *
     * @return void
     */
    protected function setUnittestConfig($arConfig)
    {
        $str = '<' . "?php\n";
        foreach ($arConfig as $name => $value) {
            $str .= '$' . $name . ' = '
                . var_export($value, true) . ";\n";
        }

        if (!is_dir($GLOBALS['datadir'])) {
            $this->fail(
                'datadir not set or not a directory: ' . $GLOBALS['datadir']
            );
        }

        $this->assertInternalType(
            'integer',
            file_put_contents($GLOBALS['datadir'] . '/config.testing-tmp.php', $str),
            'Writing config.unittest.php failed'
        );
    }
}
?>
