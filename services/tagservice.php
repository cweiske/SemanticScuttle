<?php
class TagService {
    var $db;
    var $tablename;

    function &getInstance(&$db) {
        static $instance;
        if (!isset($instance))
            $instance =& new TagService($db);
        return $instance;
    }

    function TagService(&$db) {
        $this->db =& $db;
        $this->tablename = $GLOBALS['tableprefix'] .'tags';
    }

    function getDescription($tag, $uId) {
	$query = 'SELECT tag, uId, tDescription';
	$query.= ' FROM '.$this->getTableName();
	$query.= ' WHERE tag = "'.$tag.'"';
        $query.= ' AND uId = "'.$uId.'"';

        if (!($dbresult = & $this->db->sql_query($query))) {
            message_die(GENERAL_ERROR, 'Could not get tag description', '', __LINE__, __FILE__, $query, $this->db);
            return false;
        }

        if ($row =& $this->db->sql_fetchrow($dbresult)) {
            return $row;
        } else {
            return array();
        }
    }

    function getAllDescriptions($tag) {
	$query = 'SELECT tag, uId, tDescription';
	$query.= ' FROM '.$this->getTableName();
	$query.= ' WHERE tag = "'.$tag.'"';

        if (!($dbresult = & $this->db->sql_query($query))) {
            message_die(GENERAL_ERROR, 'Could not get tag description', '', __LINE__, __FILE__, $query, $this->db);
            return false;
        }

        return $this->db->sql_fetchrowset($dbresult);
    }

    function updateDescription($tag, $uId, $desc) {
	if(count($this->getDescription($tag, $uId))>0) {
	    $query = 'UPDATE '.$this->getTableName();
	    $query.= ' SET tDescription="'.$this->db->sql_escape($desc).'"';
	    $query.= ' WHERE tag="'.$tag.'" AND uId="'.$uId.'"';
	} else {
	    $values = array('tag'=>$tag, 'uId'=>$uId, 'tDescription'=>$desc);
	    $query = 'INSERT INTO '. $this->getTableName() .' '. $this->db->sql_build_array('INSERT', $values);
	}

	$this->db->sql_transaction('begin');
        if (!($dbresult = & $this->db->sql_query($query))) {
            $this->db->sql_transaction('rollback');
            message_die(GENERAL_ERROR, 'Could not delete bookmarks', '', __LINE__, __FILE__, $query, $this->db);
            return false;
        }
	$this->db->sql_transaction('commit');
	return true;
    }

    function renameTag($uId, $oldName, $newName) {
	$query = 'UPDATE `'. $this->getTableName() .'`';
	$query.= ' SET tag="'.$newName.'"';
	$query.= ' WHERE tag="'.$oldName.'"';
	$query.= ' AND uId="'.$uId.'"';
	$this->db->sql_query($query);
    }

   

    function deleteAll() {
	$query = 'TRUNCATE TABLE `'. $this->getTableName() .'`';
	$this->db->sql_query($query);
    }

    // Properties
    function getTableName()       { return $this->tablename; }
    function setTableName($value) { $this->tablename = $value; }
}
?>
