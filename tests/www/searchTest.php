<?php
require_once dirname(__FILE__) . '/../prepare.php';
require_once 'HTTP/Request2.php';

class www_SearchTest extends TestBaseApi
{
    protected $urlPart = 'search.php';


    /**
     * Some browsers using opensearch do "urlencode" on the terms,
     * for example Firefox. Multiple terms separated with space
     * appear as "foo+bar" in the URL.
     */
    public function testMultipleTermsUrlEncoded()
    {
        $this->addBookmark(null, null, 0, null, 'unittest foo bar');
        $res = $this->getRequest('/all/foo+bar')->send();
        $this->assertSelectCount(
            '.xfolkentry', true, $res->getBody(),
            'No bookmark found', false
        );

        $res = $this->getRequest('/all/baz+bat')->send();
        $this->assertSelectCount(
            '.xfolkentry', false, $res->getBody(),
            'Bookmarks found', false
        );
    }


    /**
     * Some browsers using opensearch do "rawurlencode" on the terms,
     * for example Opera. Multiple terms separated with space
     * appear as "foo%20bar" in the URL.
     */
    public function testMultipleTermsRawUrlEncoded()
    {
        $this->addBookmark(null, null, 0, null, 'unittest foo bar');
        $res = $this->getRequest('/all/foo%20bar')->send();
        $this->assertSelectCount(
            '.xfolkentry', true, $res->getBody(),
            'No bookmark found', false
        );

        $res = $this->getRequest('/all/baz%20bat')->send();
        $this->assertSelectCount(
            '.xfolkentry', false, $res->getBody(),
            'Bookmarks found', false
        );
    }


    public function testMultipleTags()
    {
        $this->markTestSkipped(
            'FIXME: SemanticScuttle currently does not search multiple tags'
        );

        $this->addBookmark(null, null, 0, array('foo', 'bar'));
        $res = $this->getRequest('/all/foo+bar')->send();
        $this->assertSelectCount(
            '.xfolkentry', true, $res->getBody(),
            'No bookmark found', false
        );
    }

}

?>
