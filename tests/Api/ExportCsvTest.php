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
require_once 'HTTP/Request2.php';

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
        $res = rreset($this->getAuthRequest())->send();

        $this->assertEquals(200, $res->getStatus());
        //verify MIME content type
        $this->assertEquals(
            'application/csv-tab-delimited-table; charset=utf-8',
            $res->getHeader('content-type')
        );
        //we need a file name
        $this->assertNotNull($res->getHeader('content-disposition'));
    }



    /**
     * Test CSV export without bookmarks
     */
    public function testNoBookmarks()
    {
        list($req, $uid) = $this->getAuthRequest();
        $body = $req->send()->getBody();
        $csv  = $this->getCsvArray($body);

        $this->assertEquals(1, count($csv));
        $this->assertCsvHeader($csv);
    }



    /**
     * Test CSV export with some bookmarks
     */
    public function testBookmarks()
    {
        list($req, $uid) = $this->getAuthRequest();
        //public
        $this->addBookmark(
            $uid, 'http://example.org/testBookmarks', 0,
            array('unittest', 'testBookmarks'), 'mytitle'
        );
        //shared
        $this->addBookmark(
            $uid, 'http://example.org/testBookmarks-shared', 1,
            array('unittest', 'testBookmarks'), 'mytitle-shared'
        );
        //private
        $this->addBookmark(
            $uid, 'http://example.org/testBookmarks-private', 2,
            array('unittest', 'testBookmarks'), 'mytitle-private'
        );

        //private other that should not in the export
        $this->addBookmark(
            null, 'http://example.org/testBookmarks-private2', 2
        );
        //public bookmark from other people that should not be
        // exported, too
        $this->addBookmark(
            null, 'http://example.org/testBookmarks-other', 0
        );

        $body = $req->send()->getBody();
        $csv  = $this->getCsvArray($body);

        $this->assertEquals(4, count($csv));
        $this->assertCsvHeader($csv);

        $this->assertEquals('http://example.org/testBookmarks', $csv[1][0]);
        $this->assertEquals('mytitle', $csv[1][1]);
        $this->assertEquals('unittest,testbookmarks', $csv[1][2]);

        $this->assertEquals('http://example.org/testBookmarks-shared', $csv[2][0]);
        $this->assertEquals('mytitle-shared', $csv[2][1]);
        $this->assertEquals('unittest,testbookmarks', $csv[2][2]);

        $this->assertEquals('http://example.org/testBookmarks-private', $csv[3][0]);
        $this->assertEquals('mytitle-private', $csv[3][1]);
        $this->assertEquals('unittest,testbookmarks', $csv[3][2]);
    }



    /**
     * Test CSV export with tag filter
     */
    public function testTagFilter()
    {
        list($req, $uid) = $this->getAuthRequest('?tag=tag1');
        $this->addBookmark(
            $uid, 'http://example.org/tag-1', 0,
            array('unittest', 'tag1')
        );
        $this->addBookmark(
            $uid, 'http://example.org/tag-2', 0,
            array('unittest', 'tag2')
        );
        $this->addBookmark(
            $uid, 'http://example.org/tag-3', 0,
            array('unittest', 'tag1', 'tag2')
        );

        $body = $req->send()->getBody();
        $csv  = $this->getCsvArray($body);

        $this->assertEquals(3, count($csv));
        $this->assertCsvHeader($csv);

        $this->assertEquals('http://example.org/tag-1', $csv[1][0]);
        $this->assertEquals('http://example.org/tag-3', $csv[2][0]);
    }



    /**
     * Test CSV export with tag filter for multiple tags
     */
    public function testTagFilterMultiple()
    {
        list($req, $uid) = $this->getAuthRequest('?tag=tag1+tag2');
        $this->addBookmark(
            $uid, 'http://example.org/tag-1', 0,
            array('unittest', 'tag1')
        );
        $this->addBookmark(
            $uid, 'http://example.org/tag-2', 0,
            array('unittest', 'tag2')
        );
        $this->addBookmark(
            $uid, 'http://example.org/tag-3', 0,
            array('unittest', 'tag1', 'tag2')
        );

        $body = $req->send()->getBody();
        $csv  = $this->getCsvArray($body);

        $this->assertEquals(2, count($csv));
        $this->assertCsvHeader($csv);

        $this->assertEquals('http://example.org/tag-3', $csv[1][0]);
    }



    /**
     * Asserts that the CSV array contains the correct header
     *
     * @param array $csv CSV array from getCsvArray()
     *
     * @return void
     */
    protected function assertCsvHeader($csv)
    {
        $this->assertEquals(
            array('url', 'title', 'tags', 'description'),
            $csv[0]
        );
    }



    /**
     * Converts a string of CSV data to an array
     *
     * @param string $body String containing the full CSV file
     *
     * @return array Array of CSV data
     */
    protected function getCsvArray($body)
    {
        $v53 = (version_compare(PHP_VERSION, '5.3.0') === 1);

        //dead simple implementation that does not work with
        // advanced CSV files
        $ar = array();
        foreach (explode("\n", $body) as $line) {
            if ($v53) {
                $ar[] = str_getcsv($line, ';');
            } else {
                $arl = explode(';', $line);
                foreach ($arl as &$str) {
                    if (substr($str, 0, 1) == '"'
                        && substr($str, -1) == '"'
                    ) {
                        $str = substr($str, 1, -1);
                    }
                }
                $ar[] = $arl;
            }
        }
        if (count(end($ar)) == 1 && rreset(end($ar)) == '') {
            unset($ar[key($ar)]);
        }
        return $ar;
    }
}
?>