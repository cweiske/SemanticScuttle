<?php
/**
 * SemanticScuttle - your social bookmark manager.
 *
 * PHP version 5.
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Benjamin Huynh-Kim-Bang <mensonge@users.sourceforge.net>
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @author   Eric Dane <ericdane@users.sourceforge.net>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */

require_once dirname(__FILE__) . '/../prepare.php';
require_once 'HTTP/Request2.php';

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Api_ExportCsvTest::main');
}

/**
 * Unit tests for the SemanticScuttle csv export API
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Benjamin Huynh-Kim-Bang <mensonge@users.sourceforge.net>
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @author   Eric Dane <ericdane@users.sourceforge.net>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */
class Api_ExportCsvTest extends TestBaseApi
{
    protected $us;
    protected $bs;
    protected $urlPart = 'api/export_csv.php';



    /**
     * Used to run this test class standalone
     *
     * @return void
     */
    public static function main()
    {
        require_once 'PHPUnit/TextUI/TestRunner.php';
        PHPUnit_TextUI_TestRunner::run(
            new PHPUnit_Framework_TestSuite(__CLASS__)
        );
    }



    /**
     * Test if authentication is required when sending no auth data
     */
    public function testAuthWithoutAuthData()
    {
        $req = $this->getRequest(null, false);
        $res = $req->send();
        $this->assertEquals(401, $res->getStatus());
    }



    /**
     * Test if authentication is required when sending wrong user data

     */
    public function testAuthWrongCredentials()
    {
        $req = $this->getRequest(null, false);
        $req->setAuth('user', 'password', HTTP_Request2::AUTH_BASIC);
        $res = $req->send();
        $this->assertEquals(401, $res->getStatus());
    }



    /**
     * Test MIME content type and filename header fields
     */
    public function testMimeTypeFilename()
    {
        $res = $this->getRequest()->send();

        $this->assertEquals(200, $res->getStatus());
        //verify MIME content type
        $this->assertEquals(
            'application/csv-tab-delimited-table; charset=utf-8',
            $res->getHeader('content-type')
        );
        //we need a file name
        $this->assertNotNull($res->getHeader('content-disposition'));
    }
}

if (PHPUnit_MAIN_METHOD == 'Api_ExportCsvTest::main') {
    Api_ExportCsvTest::main();
}
?>