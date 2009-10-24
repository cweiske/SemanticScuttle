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
 * Base class for services utilizing the database.
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author Christian Weiske <cweiske@cweiske.de>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */
class SemanticScuttle_DbService extends SemanticScuttle_Service
{
    /**
     * Database object
     *
     * @var sql_db
     */
    protected $db;



    /**
     * Database table name
     *
     * @var string
     */
    protected $tablename;



    /**
     * Returns database table name
     *
     * @return string Table name
     */
    public function getTableName() 
    {
        return $this->tablename;
    }



    /**
     * Set the database table name
     *
     * @param string $value New table name
     *
     * @return void
     */
    function setTableName($value)
    {
        $this->tablename = $value;
    }

}
