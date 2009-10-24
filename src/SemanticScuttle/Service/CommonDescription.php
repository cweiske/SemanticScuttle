<?php
class SemanticScuttle_Service_CommonDescription extends SemanticScuttle_DbService
{
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
        $this->tablename = $GLOBALS['tableprefix'] .'commondescription';
    }

    function addTagDescription($tag, $desc, $uId, $time) {
        // Check if no modification
        $lastDesc = $this->getLastTagDescription($tag);
        if($lastDesc['cdDescription'] == $desc) {
            return true;
        }

        // If modification
        $datetime = gmdate('Y-m-d H:i:s', $time);
        $values = array('tag'=>$tag, 'cdDescription'=>$desc, 'uId'=>$uId, 'cdDatetime'=>$datetime);
        $sql = 'INSERT INTO '. $this->getTableName() .' '. $this->db->sql_build_array('INSERT', $values);

        if (!($dbresult =& $this->db->sql_query($sql))) {
            $this->db->sql_transaction('rollback');
            message_die(GENERAL_ERROR, 'Could not add tag description', '', __LINE__, __FILE__, $sql, $this->db);
            return false;
        }

        return true;
    }

    function getLastTagDescription($tag) {
        $query = "SELECT *";
        $query.= " FROM `". $this->getTableName() ."`";
        $query.= " WHERE tag='".$tag."'";
        $query.= " ORDER BY cdDatetime DESC";

        if (!($dbresult = & $this->db->sql_query_limit($query, 1, 0))) {
            message_die(GENERAL_ERROR, 'Could not get tag description', '', __LINE__, __FILE__, $query, $this->db);
            return false;
        }

        if ($row =& $this->db->sql_fetchrow($dbresult)) {
            return $row;
        } else {
            return false;
        }
    }

    function getAllTagsDescription($tag) {
        $query = "SELECT *";
        $query.= " FROM `". $this->getTableName() ."`";
        $query.= " WHERE tag='".$tag."'";
        $query.= " ORDER BY cdDatetime DESC";

        if (!($dbresult = & $this->db->sql_query($query))) {
            message_die(GENERAL_ERROR, 'Could not get tag descriptions', '', __LINE__, __FILE__, $query, $this->db);
            return false;
        }

        return $this->db->sql_fetchrowset($dbresult);

    }

    function getDescriptionById($cdId) {
        $query = "SELECT *";
        $query.= " FROM `". $this->getTableName() ."`";
        $query.= " WHERE cdId='".$cdId."'";

        if (!($dbresult = & $this->db->sql_query($query))) {
            message_die(GENERAL_ERROR, 'Could not get tag descriptions', '', __LINE__, __FILE__, $query, $this->db);
            return false;
        }

        if ($row =& $this->db->sql_fetchrow($dbresult)) {
            return $row;
        } else {
            return false;
        }

    }

    function addBookmarkDescription($bHash, $title, $desc, $uId, $time) {
        // Check if no modification
        $lastDesc = $this->getLastBookmarkDescription($bHash);
        if($lastDesc['cdTitle'] == $title && $lastDesc['cdDescription'] == $desc) {
            return true;
        }

        // If modification
        $datetime = gmdate('Y-m-d H:i:s', $time);
        $values = array('bHash'=>$bHash, 'cdTitle'=>$title, 'cdDescription'=>$desc, 'uId'=>$uId, 'cdDatetime'=>$datetime);
        $sql = 'INSERT INTO '. $this->getTableName() .' '. $this->db->sql_build_array('INSERT', $values);

        if (!($dbresult =& $this->db->sql_query($sql))) {
            $this->db->sql_transaction('rollback');
            message_die(GENERAL_ERROR, 'Could not add bookmark description', '', __LINE__, __FILE__, $sql, $this->db);
            return false;
        }
        return true;
    }

    function getLastBookmarkDescription($bHash) {
        $query = "SELECT *";
        $query.= " FROM `". $this->getTableName() ."`";
        $query.= " WHERE bHash='".$bHash."'";
        $query.= " ORDER BY cdDatetime DESC";

        if (!($dbresult = & $this->db->sql_query_limit($query, 1, 0))) {
            message_die(GENERAL_ERROR, 'Could not get bookmark description', '', __LINE__, __FILE__, $query, $this->db);
            return false;
        }

        if ($row =& $this->db->sql_fetchrow($dbresult)) {
            return $row;
        } else {
            return false;
        }
    }

    function getAllBookmarksDescription($bHash) {
        $query = "SELECT *";
        $query.= " FROM `". $this->getTableName() ."`";
        $query.= " WHERE bHash='".$bHash."'";
        $query.= " ORDER BY cdDatetime DESC";

        if (!($dbresult = & $this->db->sql_query($query))) {
            message_die(GENERAL_ERROR, 'Could not get bookmark descriptions', '', __LINE__, __FILE__, $query, $this->db);
            return false;
        }

        return $this->db->sql_fetchrowset($dbresult);

    }

    function deleteDescriptionsForUser($uId){
        $query = 'DELETE FROM '. $this->getTableName() . ' WHERE uId = '. intval($uId);

        $this->db->sql_transaction('begin');
        if (!($dbresult = & $this->db->sql_query($query))) {
            $this->db->sql_transaction('rollback');
            message_die(GENERAL_ERROR, 'Could not delete user descriptions', '',
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
