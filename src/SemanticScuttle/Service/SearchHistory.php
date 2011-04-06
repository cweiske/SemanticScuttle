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

/**
 * SemanticScuttle search history service.
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Benjamin Huynh-Kim-Bang <mensonge@users.sourceforge.net>
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @author   Eric Dane <ericdane@users.sourceforge.net>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */
class SemanticScuttle_Service_SearchHistory extends SemanticScuttle_DbService
{
    /**
     * Size of the search history.
     * If the number of logged searches is larger than this,
     * adding a new search will delete the oldest one automatically.
     *
     * Use -1 to deactivate automatic deletion.
     *
     * @var integer
     */
    public $sizeSearchHistory;



    /**
     * Returns the single service instance
     *
     * @param DB $db Database object
     *
     * @return SemanticScuttle_Service
     */
    public static function getInstance($db)
    {
        static $instance;
        if (!isset($instance)) {
            $instance = new self($db);
        }
        return $instance;
    }



    /**
     * Creates a new instance.
     *
     * Sets $this->sizeSearchHistory to $GLOBALS['sizeSearchHistory'] or 10
     * if the global variable is not defined.
     *
     * @param DB $db Database object
     */
    public function __construct($db)
    {
        $this->db = $db;
        $this->tablename = $GLOBALS['tableprefix'] . 'searchhistory';
        if (isset($GLOBALS['sizeSearchHistory'])) {
            $this->sizeSearchHistory = $GLOBALS['sizeSearchHistory'];
        } else {
            $this->sizeSearchHistory = 10;
        }
    }



    /**
     * Adds a new search to the search history.
     * Automatically deletes the oldest search when the number of
     * searches is larger than $sizeSearchHistory.
     *
     * @param string  $terms     Search terms separated by spaces
     * @param string  $range     - 'all' - search was in all bookmarks
     *                           - 'watchlist' - searched in watchlist
     *                           - any username to show that the search happened
     *                             in his own bookmarks.
     * @param integer $nbResults Number of search result rows
     * @param integer $uId       ID of user that searched
     *
     * @return boolean True if it has been added, false if not
     */
    public function addSearch($terms, $range, $nbResults, $uId = 0)
    {
        if (strlen($terms) == 0) {
            return false;
        }
        $datetime = gmdate('Y-m-d H:i:s', time());

        //Insert values
        $values = array(
            'shTerms'     => $terms,
            'shRange'     => $range,
            'shDatetime'  => $datetime,
            'shNbResults' => $nbResults,
            'uId'         => $uId
        );
        $sql = 'INSERT INTO ' . $this->getTableName()
            . ' ' . $this->db->sql_build_array('INSERT', $values);

        $this->db->sql_transaction('begin');
        if (!($dbresult = $this->db->sql_query($sql))) {
            $this->db->sql_transaction('rollback');
            message_die(
                GENERAL_ERROR, 'Could not insert search history',
                '', __LINE__, __FILE__, $sql, $this->db
            );
            return false;
        }

        if ($this->sizeSearchHistory != -1
            && $this->countSearches() > $this->sizeSearchHistory
        ) {
            $this->deleteOldestSearch();
        }

        return true;
    }



    /**
     * Returns searches with the given features.
     *
     * @param string  $range       - 'all' - search was in all bookmarks
     *                             - 'watchlist' - searched in watchlist
     *                             - any username to show that the search happened
     *                               in his own bookmarks.
     * @param integer $uId         Id of the user who searched. null for any users
     * @param integer $nb          Number of bookmarks to retrieve (paging)
     * @param integer $start       Number of bookmark to begin with (paging)
     * @param boolean $distinct    If the search terms shall be distinct
     * @param boolean $withResults Only return searches that had at least one result
     *
     * @return array Array of search history database rows
     */
    public function getAllSearches(
        $range = null, $uId = null, $nb = null,
        $start = null, $distinct = false, $withResults = false
    ) {
        $sql = 'SELECT DISTINCT(shTerms),'
            . ' shId, shRange, shNbResults, shDatetime, uId';
        $sql.= ' FROM '. $this->getTableName();
        $sql.= ' WHERE 1=1';
        if ($range != null) {
            $sql.= ' AND shRange = "'.$range.'"';
        } else {
            $sql.= ' AND shRange = "all"';
        }
        if ($uId != null) {
            $sql.= ' AND uId = '.$uId;
        }
        if ($withResults == true) {
            $sql.= ' AND shNbResults > 0';
        }
        if ($distinct) {
            $sql.= ' GROUP BY shTerms';
        }
        $sql.= ' ORDER BY shId DESC';

        if (!($dbresult = $this->db->sql_query_limit($sql, $nb, $start))) {
            message_die(
                GENERAL_ERROR, 'Could not get searches',
                '', __LINE__, __FILE__, $sql, $this->db
            );
            return false;
        }

        $searches = array();
        while ($row = $this->db->sql_fetchrow($dbresult)) {
            $searches[] = $row;
        }
        $this->db->sql_freeresult($dbresult);
        return $searches;
    }



    /**
     * Counts the number of searches that have been made in total.
     *
     * @return integer Number of searches
     */
    public function countSearches()
    {
        $sql = 'SELECT COUNT(*) AS `total` FROM '. $this->getTableName();
        if (!($dbresult = $this->db->sql_query($sql))
            || (!($row = & $this->db->sql_fetchrow($dbresult)))
        ) {
            message_die(
                GENERAL_ERROR, 'Could not get total searches',
                '', __LINE__, __FILE__, $sql, $this->db
            );
            return false;
        }
        $this->db->sql_freeresult($dbresult);
        return $row['total'];
    }



    /**
     * This function allows to limit the number of saved searches
     * by deleting the oldest one
     *
     * @return boolean True when all went well, false in case of an error
     */
    public function deleteOldestSearch()
    {
        $sql = 'DELETE FROM '.$this->getTableName();
        // warning: here the limit is important
        $sql .= ' ORDER BY shId ASC LIMIT 1';

        $this->db->sql_transaction('begin');
        if (!($dbresult = $this->db->sql_query($sql))) {
            $this->db->sql_transaction('rollback');
            message_die(
                GENERAL_ERROR, 'Could not delete bookmarks',
                '', __LINE__, __FILE__, $query, $this->db
            );
            return false;
        }

        return true;
    }



    /**
     * Deletes all search history entries that have been made by the user
     * with the given ID.
     *
     * @param integer $uId ID of the user
     *
     * @return boolean True when all went well, false in case of an error
     */
    public function deleteSearchHistoryForUser($uId)
    {
        $query = 'DELETE FROM '. $this->getTableName()
            . ' WHERE uId = ' . intval($uId);

        if (!($dbresult = $this->db->sql_query($query))) {
            message_die(
                GENERAL_ERROR, 'Could not delete search history', '',
                __LINE__, __FILE__, $query, $this->db
            );
            return false;
        }

        return true;
    }



    /**
     * Deletes all search history entries.
     *
     * @return void
     */
    public function deleteAll()
    {
        $query = 'TRUNCATE TABLE `'. $this->getTableName() .'`';
        $this->db->sql_query($query);
    }

}
?>
