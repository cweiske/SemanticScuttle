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
    var $sizeSearchHistory;

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

    public function __construct($db)
    {
        $this->db = $db;
        $this->tablename = $GLOBALS['tableprefix'] .'searchhistory';
        if(isset($GLOBALS['sizeSearchHistory'])) {
            $this->sizeSearchHistory = $GLOBALS['sizeSearchHistory'];
        } else {
            $this->sizeSearchHistory = 10;
        }
    }

    function addSearch($terms, $range, $nbResults, $uId=0) {
        if(strlen($terms) == 0) {
            return false;
        }
        $datetime = gmdate('Y-m-d H:i:s', time());

        //Insert values
        $values = array('shTerms'=>$terms, 'shRange'=>$range, 'shDatetime'=>$datetime, 'shNbResults'=>$nbResults, 'uId'=>$uId);
        $sql = 'INSERT INTO '. $this->getTableName() .' '. $this->db->sql_build_array('INSERT', $values);
        $this->db->sql_transaction('begin');
        if (!($dbresult = & $this->db->sql_query($sql))) {
            $this->db->sql_transaction('rollback');
            message_die(GENERAL_ERROR, 'Could not insert search history', '', __LINE__, __FILE__, $sql, $this->db);
            return false;
        }

        if($this->sizeSearchHistory != -1 &&
        $this->countSearches() > $this->sizeSearchHistory) {
            $this->deleteOldestSearch();
        }
    }

    function getAllSearches($range = NULL, $uId = NULL, $nb = NULL, $start = NULL, $distinct = false, $withResults = false) {
        $sql = 'SELECT DISTINCT(shTerms), shId, shRange, shNbResults, shDatetime, uId';
        $sql.= ' FROM '. $this->getTableName();
        $sql.= ' WHERE 1=1';
        if($range != NULL) {
            $sql.= ' AND shRange = "'.$range.'"';
        } else {
            $sql.= ' AND shRange = "all"';
        }
        if($uId != NULL) {
            $sql.= ' AND uId = '.$uId;
        }
        if($withResults = true) {
            $sql.= ' AND shNbResults > 0';
        }
        if($distinct) {
            $sql.= ' GROUP BY shTerms';
        }
        $sql.= ' ORDER BY shId DESC';

        if (!($dbresult = & $this->db->sql_query_limit($sql, $nb, $start))) {
            message_die(GENERAL_ERROR, 'Could not get searches', '', __LINE__, __FILE__, $sql, $this->db);
            return false;
        }

        $searches = array();
        while ($row = & $this->db->sql_fetchrow($dbresult)) {
            $searches[] = $row;
        }
        $this->db->sql_freeresult($dbresult);
        return $searches;
    }

    function countSearches() {
        $sql = 'SELECT COUNT(*) AS `total` FROM '. $this->getTableName();
        if (!($dbresult = & $this->db->sql_query($sql)) || (!($row = & $this->db->sql_fetchrow($dbresult)))) {
            message_die(GENERAL_ERROR, 'Could not get total searches', '', __LINE__, __FILE__, $sql, $this->db);
            return false;
        }
        $this->db->sql_freeresult($dbresult);
        return $row['total'];
    }

    /* This function allows to limit the number of saved searches
     by deleting the oldest one */
    function deleteOldestSearch() {
        $sql = 'DELETE FROM '.$this->getTableName();
        $sql.= ' ORDER BY shId ASC LIMIT 1';  // warning: here the limit is important

        $this->db->sql_transaction('begin');
        if (!($dbresult = & $this->db->sql_query($sql))) {
            $this->db->sql_transaction('rollback');
            message_die(GENERAL_ERROR, 'Could not delete bookmarks', '', __LINE__, __FILE__, $query, $this->db);
            return false;
        }
    }

    function deleteSearchHistoryForUser($uId) {
        $query = 'DELETE FROM '. $this->getTableName() .' WHERE uId = '.        intval($uId);

        if (!($dbresult = & $this->db->sql_query($query))) {
            message_die(GENERAL_ERROR, 'Could not delete search history', '',
            __LINE__, __FILE__, $query, $this->db);
            return false;
        }

        return true;
    }

    function deleteAll() {
        $query = 'TRUNCATE TABLE `'. $this->getTableName() .'`';
        $this->db->sql_query($query);
    }

}
?>
