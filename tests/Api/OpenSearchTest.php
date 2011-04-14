<?php


class Api_OpenSearchTest extends TestBaseApi
{
    protected $urlPart = '';



    public function testOpenSearchAvailable()
    {
        $req  = $this->getRequest();
        $xhtml = $req->send()->getBody();

        $xml = simplexml_load_string($xhtml);
        $xml->registerXPathNamespace('h', reset($xml->getDocNamespaces()));

        $this->assertInstanceOf(
            'SimpleXMLElement', $xml,
            'SemanticScuttle main page XHTML could not be loaded - maybe invalid?'
        );

        $arElements = $xml->xpath(
            '//h:head/h:link'
            . '[@rel="search" and @type="application/opensearchdescription+xml"]'
        );
        $this->assertEquals(
            1, count($arElements),
            'OpenSearch link in HTML is missing'
        );
        $searchDescUrl = (string)$arElements[0]['href'];
        $this->assertNotNull($searchDescUrl, 'Search description URL is empty');

        $req = new HTTP_Request2($searchDescUrl);
        $res = $req->send();
        $this->assertEquals(
            200, $res->getStatus(),
            'HTTP response status code is not 200'
        );

        $this->assertEquals(
            $GLOBALS['unittestUrl'] . 'api/opensearch.php',
            $searchDescUrl,
            'OpenSearch URL found, but it is not the expected one.'
            . ' It may be that you misconfigured the "unittestUrl" setting'
        );
    }

}

?>