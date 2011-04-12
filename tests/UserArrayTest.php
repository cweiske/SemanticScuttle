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
 * Unit tests for the SemanticScuttle user array model.
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */
class UserArrayTest extends PHPUnit_Framework_TestCase
{

    public function testGetNameLongName()
    {
        $this->assertEquals(
            'John Doe',
            SemanticScuttle_Model_UserArray::getName(
                array(
                    'name'     => 'John Doe',
                    'username' => 'jdoe'
                )
            )
        );
    }

    public function testGetNameUsernameIfNameIsEmpty()
    {
        $this->assertEquals(
            'jdoe',
            SemanticScuttle_Model_UserArray::getName(
                array(
                    'name'     => '',
                    'username' => 'jdoe'
                )
            )
        );
    }

    public function testGetNameUsernameIfNameIsNotSet()
    {
        $this->assertEquals(
            'jdoe',
            SemanticScuttle_Model_UserArray::getName(
                array(
                    'username' => 'jdoe'
                )
            )
        );
    }

}

?>