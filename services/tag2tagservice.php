<?php
class Tag2TagService {
    var $db;
    var $tablename;

    function &getInstance(&$db) {
        static $instance;
        if (!isset($instance))
            $instance =& new Tag2TagService($db);
        return $instance;
    }

    function Tag2TagService(&$db) {
        $this->db =& $db;
        $this->tablename = $GLOBALS['tableprefix'] .'tags2tags';
    }

    function addLinkedTags($tag1, $tag2, $relationType, $uId) {
	if($tag1 == $tag2) {
		return false;
	}
	$values = array('tag1' => $tag1, 'tag2' => $tag2, 'relationType'=> $relationType, 'uId'=> $uId);
	$query = 'INSERT INTO '. $this->getTableName() .' '. $this->db->sql_build_array('INSERT', $values);
//die($query);
        if (!($dbresult =& $this->db->sql_query($query))) {
        	$this->db->sql_transaction('rollback');
        	message_die(GENERAL_ERROR, 'Could not attach tag to tag', '', __LINE__, __FILE__, $query, $this->db);
                return false;
        }
	$this->db->sql_transaction('commit');
	return true;
    }

    function getLinkedTags($tag1, $relationType, $uId = -1) {
	// Set up the SQL query.
        $query = "SELECT DISTINCT tag2 as 'tag' FROM `". $this->getTableName() ."`";
	$query.= " WHERE tag1 = '" .$tag1 ."'";
	if($relationType) {
	    $query.= " AND relationType = '". $relationType ."'";
	}
	if($uId>0) {
	    $query.= " AND uId = '".$uId."'";
	}

        if (! ($dbresult =& $this->db->sql_query_limit($query, $limit)) ){
            message_die(GENERAL_ERROR, 'Could not get related tags', '', __LINE__, __FILE__, $query, $this->db);
            return false;
        }

        $rowset = $this->db->sql_fetchrowset($dbresult);
	$output = array();
	foreach($rowset as $row) {
	    $output[] = $row['tag'];
	}
	return $output;
    }

    /* TODO: clean the outputs to obtain homogenous ones*/
    function getAllLinkedTags($tag1, $relationType, $uId, $asFlatList=true, $stopList=array()) {
	if(in_array($tag1, $stopList)) {
	    return $tag1;
	}
	$linkedTags = $this->getLinkedTags($tag1, $relationType, $uId);
	if(count($linkedTags) == 0) {
	    return $tag1;
	} else {
	    $output = array();
	    if($asFlatList == true) {
		$output[$tag1] = $tag1;
	    } else {
		$output = array('node'=>$tag1);
	    }

	    $stopList[] = $tag1;
	    foreach($linkedTags as $linkedTag) {
		$allLinkedTags = $this->getAllLinkedTags($linkedTag, $relationType, $uId, $asFlatList, $stopList);
		if($asFlatList == true) {
		    if(is_array($allLinkedTags)) {
			$output = array_merge($output, $allLinkedTags);
		    } else {
		        $output[$allLinkedTags] = $allLinkedTags;
		    }
		} else {
		    $output[] = $allLinkedTags;
		}
	    }
	}
	return $output;
    }

    function getOrphewTags($relationType, $uId = 0) {
	$query = "SELECT DISTINCT tag1 as tag FROM `". $this->getTableName() ."`";
	$query.= " WHERE tag1 <> ALL";
	$query.= " (SELECT DISTINCT tag2 FROM `". $this->getTableName() ."`";
	$query.= " WHERE relationType = '".$relationType."'";
	if($uId > 0) {
	    $query.= " AND uId = '".$uId."'";
	}
	$query.= ")";
	if($uId > 0) {
	    $query.= " AND uId = '".$uId."'";
	}

	//die($query);

        if (! ($dbresult =& $this->db->sql_query_limit($query, $limit)) ){
            message_die(GENERAL_ERROR, 'Could not get linked tags', '', __LINE__, __FILE__, $query, $this->db);
            return false;
        }
        return $this->db->sql_fetchrowset($dbresult);
    }

    function existsLinkedTags($tag1, $tag2, $relationType, $uId) {
	$query = "SELECT tag1, tag2, relationType, uId FROM `". $this->getTableName() ."`";
	$query.= " WHERE tag1 = '" .$tag1 ."'";
	$query.= " AND tag2 = '".$tag2."'";
	$query.= " AND relationType = '". $relationType ."'";
	$query.= " AND uId = '".$uId."'";

        return $this->db->sql_numrows($dbresult) > 0;
    }

    function removeLinkedTags($tag1, $tag2, $relationType, $uId) {
	$query = 'DELETE FROM '. $this->getTableName();
	$query.= ' WHERE tag1 = "'. $tag1 .'"';
	$query.= ' AND tag2 = "'. $tag2 .'"';
	$query.= ' AND relationType = "'. $relationType .'"';
	$query.= ' AND uId = "'. $uId .'"';

        if (!($dbresult =& $this->db->sql_query($query))) {
            message_die(GENERAL_ERROR, 'Could not remove tag relation', '', __LINE__, __FILE__, $query, $this->db);
            return false;
        }

        return true;
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
