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

/**
 * Unit tests for the SemanticScuttle Bookmark model
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */
class Model_BookmarkTest extends TestBase
{
    public function testIsValidUrlValid()
    {
        $this->assertTrue(
            SemanticScuttle_Model_Bookmark::isValidUrl(
                'http://example.org/foo/bar?baz=foorina'
            )
        );
        $this->assertTrue(
            SemanticScuttle_Model_Bookmark::isValidUrl(
                'https://example.org/'
            )
        );
        $this->assertTrue(
            SemanticScuttle_Model_Bookmark::isValidUrl(
                'ftp://user:pass@example.org/'
            )
        );
        $this->assertTrue(
            SemanticScuttle_Model_Bookmark::isValidUrl(
                'mailto:cweiske@example.org'
            )
        );
    }

    public function testIsValidUrlInvalid()
    {
        $this->assertFalse(
            SemanticScuttle_Model_Bookmark::isValidUrl(
                'javascript:alert("foo")'
            )
        );
        $this->assertFalse(
            SemanticScuttle_Model_Bookmark::isValidUrl(
                'foo://example.org/foo/bar'
            )
        );
    }

}

?>