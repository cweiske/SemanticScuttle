<?php
class SemanticScuttle_Service_Tag2Tag extends SemanticScuttle_DbService
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


	function __construct(&$db)
    {
		$this->db =& $db;
		$this->tablename = $GLOBALS['tableprefix'] .'tags2tags';
	}

	function addLinkedTags($tag1, $tag2, $relationType, $uId) {
		$tagservice =SemanticScuttle_Service_Factory::get('Tag');
		$tag1 = $tagservice->normalize($tag1);
		$tag2 = $tagservice->normalize($tag2);

		if($tag1 == $tag2 || strlen($tag1) == 0 || strlen($tag2) == 0
		|| ($relationType != ">" && $relationType != "=")
		|| !is_numeric($uId) || $uId<=0
		|| ($this->existsLinkedTags($tag1, $tag2, $relationType, $uId))) {
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

		// Update stats and cache
		$this->update($tag1, $tag2, $relationType, $uId);

		return true;
	}

	// Return linked tags just for admin users
	function getAdminLinkedTags($tag, $relationType, $inverseRelation = false, $stopList = array()) {
		// look for admin ids
		$userservice = SemanticScuttle_Service_Factory :: get('User');
		$adminIds = $userservice->getAdminIds();
		
		//ask for their linked tags
		return $this->getLinkedTags($tag, $relationType, $adminIds, $inverseRelation, $stopList);
	}
	
	// Return the target linked tags. If inverseRelation is true, return the source linked tags.
	function getLinkedTags($tag, $relationType, $uId = null, $inverseRelation = false, $stopList = array()) {
		// Set up the SQL query.
		if($inverseRelation) {
			$queriedTag = "tag1";
			$givenTag = "tag2";
		} else {
			$queriedTag = "tag2";
			$givenTag = "tag1";
		}

		$query = "SELECT DISTINCT ". $queriedTag ." as 'tag'";
		$query.= " FROM `". $this->getTableName() ."`";
		$query.= " WHERE 1=1";
		if($tag !=null) {
			$query.= " AND ". $givenTag ." = '". $tag ."'";
		}
		if($relationType) {
			$query.= " AND relationType = '". $relationType ."'";
		}
		if(is_array($uId)) {
			$query.= " AND ( 1=0 "; //tricks always false			
			foreach($uId as $u) {
				$query.= " OR uId = '".$u."'";
			}
			$query.= " ) "; 
		} elseif($uId != null) {
			$query.= " AND uId = '".$uId."'";
		}
		//die($query);
		if (! ($dbresult =& $this->db->sql_query($query)) ){
			message_die(GENERAL_ERROR, 'Could not get related tags', '', __LINE__, __FILE__, $query, $this->db);
			return false;
		}

		$rowset = $this->db->sql_fetchrowset($dbresult);
		$output = array();
		foreach($rowset as $row) {
			if(!in_array($row['tag'], $stopList)) {
				$output[] = $row['tag'];
			}
		}

		//bijective case for '='
		if($relationType == '=' && $inverseRelation == false) {
			//$stopList[] = $tag;
			$bijectiveOutput = $this->getLinkedTags($tag, $relationType, $uId, true, $stopList);
			$output = array_merge($output, $bijectiveOutput);
			//$output = array_unique($output); // remove duplication
		}

		$this->db->sql_freeresult($dbresult);
		return $output;
	}

	/*
	 * Returns all linked tags (all descendants if relation is >,
	 * all synonyms if relation is = )
	 * $stopList allows to avoid cycle (a > b > a) between tags
	 */
	function getAllLinkedTags($tag1, $relationType, $uId, $stopList=array()) {
		if(in_array($tag1, $stopList) || $tag1 == '') {
			return array();
		}

		// try to find data in cache
		$tcs = SemanticScuttle_Service_Factory::get('TagCache');
		if(count($stopList) == 0) {
			$activatedCache = true;
		} else {
			$activatedCache = false;
		}

		// look for existing links
		$stopList[] = $tag1;
		$linkedTags = $this->getLinkedTags($tag1, $relationType, $uId, false, $stopList);
		if($relationType != '=') {
			$linkedTags = array_merge($linkedTags, $this->getLinkedTags($tag1, '=', $uId, false, $stopList));
		}

		if(count($linkedTags) == 0) {
			return array();

		} else {
			// use cache if possible
			if($activatedCache) {
				if($relationType == '>') {
					$output = $tcs->getChildren($tag1, $uId);
				} elseif($relationType == '=') {
					$output = $tcs->getSynonyms($tag1, $uId);
				}
				if(count($output)>0) {
					return $output;
				}
			}

			// else compute the links
			$output = array();

			foreach($linkedTags as $linkedTag) {
				$allLinkedTags = $this->getAllLinkedTags($linkedTag, $relationType, $uId, $stopList);
				$output[] = $linkedTag;
				if(is_array($allLinkedTags)) {
					$output = array_merge($output, $allLinkedTags);
				} else {
					$output[] = $allLinkedTags;
				}
			}

			// and save in cache
			if($activatedCache == true && $uId>0) {
				$tcs->updateTag($tag1, $relationType, $output, $uId);
			}
				
			//$output = array_unique($output); // remove duplication
			return $output;

		}
	}

	function getOrphewTags($relationType, $uId = 0, $limit = null, $orderBy = null) {
		$query = "SELECT DISTINCT tts.tag1 as tag";
		$query.= " FROM `". $this->getTableName() ."` tts";
		if($orderBy != null) {
			$tsts =SemanticScuttle_Service_Factory::get('TagStat');
			$query.= ", ".$tsts->getTableName() ." tsts";
		}
		$query.= " WHERE tts.tag1 <> ALL";
		$query.= " (SELECT DISTINCT tag2 FROM `". $this->getTableName() ."`";
		$query.= " WHERE relationType = '".$relationType."'";
		if($uId > 0) {
			$query.= " AND uId = '".$uId."'";
		}
		$query.= ")";
		if($uId > 0) {
			$query.= " AND tts.uId = '".$uId."'";
		}

		switch($orderBy) {
	  case "nb":
	  	$query.= " AND tts.tag1 = tsts.tag1";
	  	$query.= " AND tsts.relationType = '".$relationType."'";
	  	if($uId > 0) {
	  		$query.= " AND tsts.uId = ".$uId;
	  	}
	  	$query.= " ORDER BY tsts.nb DESC";
	  	break;
	  case "depth": // by nb of descendants
	  	$query.= " AND tts.tag1 = tsts.tag1";
	  	$query.= " AND tsts.relationType = '".$relationType."'";
	  	if($uId > 0) {
	  		$query.= " AND tsts.uId = ".$uId;
	  	}
	  	$query.= " ORDER BY tsts.depth DESC";
	  	break;
	  case "nbupdate":
	  	$query.= " AND tts.tag1 = tsts.tag1";
	  	$query.= " AND tsts.relationType = '".$relationType."'";
	  	if($uId > 0) {
	  		$query.= " AND tsts.uId = ".$uId;
	  	}
	  	$query.= " ORDER BY tsts.nbupdate DESC";
	  	break;
		}

		if($limit != null) {
			$query.= " LIMIT 0,".$limit;
		}

		if (! ($dbresult =& $this->db->sql_query($query)) ){
			message_die(GENERAL_ERROR, 'Could not get linked tags', '', __LINE__, __FILE__, $query, $this->db);
			return false;
		}
		$output = $this->db->sql_fetchrowset($dbresult);
		$this->db->sql_freeresult($dbresult);
		return $output;
	}

	function getMenuTags($uId) {
		if(strlen($GLOBALS['menuTag']) < 1) {
			return array();
		} else {
			// we don't use the getAllLinkedTags function in order to improve performance
			$query = "SELECT tag2 as 'tag', COUNT(tag2) as 'count'";
			$query.= " FROM `". $this->getTableName() ."`";
			$query.= " WHERE tag1 = '".$GLOBALS['menuTag']."'";
			$query.= " AND relationType = '>'";
			if($uId > 0) {
				$query.= " AND uId = '".$uId."'";
			}
			$query.= " GROUP BY tag2";
			$query.= " ORDER BY count DESC";
			$query.= " LIMIT 0, ".$GLOBALS['maxSizeMenuBlock'];

			if (! ($dbresult =& $this->db->sql_query($query)) ){
				message_die(GENERAL_ERROR, 'Could not get linked tags', '', __LINE__, __FILE__, $query, $this->db);
				return false;
			}
			$output = $this->db->sql_fetchrowset($dbresult);
			$this->db->sql_freeresult($dbresult);
			return $output;
		}
	}


	function existsLinkedTags($tag1, $tag2, $relationType, $uId) {

		//$tag1 = mysql_real_escape_string($tag1);
		//$tag2 = mysql_real_escape_string($tag2);

		$query = "SELECT tag1, tag2, relationType, uId FROM `". $this->getTableName() ."`";
		$query.= " WHERE tag1 = '" .$tag1 ."'";
		$query.= " AND tag2 = '".$tag2."'";
		$query.= " AND relationType = '". $relationType ."'";
		$query.= " AND uId = '".$uId."'";

		//echo($query."<br>\n");

		return $this->db->sql_numrows($this->db->sql_query($query)) > 0;
	}

	function getLinks($uId) {
		$query = "SELECT tag1, tag2, relationType, uId FROM `". $this->getTableName() ."`";
		$query.= " WHERE 1=1";
		if($uId > 0) {
			$query.= " AND uId = '".$uId."'";
		}

		return $this->db->sql_fetchrowset($this->db->sql_query($query));
	}

	function removeLinkedTags($tag1, $tag2, $relationType, $uId) {
		if(($tag1 != '' && $tag1 == $tag2) ||
		($relationType != ">" && $relationType != "=" && $relationType != "") ||
		($tag1 == '' && $tag2 == '')) {
			return false;
		}
		$query = 'DELETE FROM '. $this->getTableName();
		$query.= ' WHERE 1=1';
		$query.= strlen($tag1)>0 ? ' AND tag1 = "'. $tag1 .'"' : '';
		$query.= strlen($tag2)>0 ? ' AND tag2 = "'. $tag2 .'"' : '';
		$query.= strlen($relationType)>0 ? ' AND relationType = "'. $relationType .'"' : '';
		$query.= strlen($uId)>0 ? ' AND uId = "'. $uId .'"' : '';

		if (!($dbresult =& $this->db->sql_query($query))) {
			message_die(GENERAL_ERROR, 'Could not remove tag relation', '', __LINE__, __FILE__, $query, $this->db);
			return false;
		}


		// Update stats and cache
		$this->update($tag1, $tag2, $relationType, $uId);
		
		$this->db->sql_freeresult($dbresult);
		return true;
	}
	
	function removeLinkedTagsForUser($uId) {
		$query = 'DELETE FROM '. $this->getTableName();
		$query.= ' WHERE uId = "'. $uId .'"';

		if (!($dbresult =& $this->db->sql_query($query))) {
			message_die(GENERAL_ERROR, 'Could not remove tag relation', '', __LINE__, __FILE__, $query, $this->db);
			return false;
		}


		// Update stats and cache
		$this->update('', '', '', $uId);
		
		$this->db->sql_freeresult($dbresult);
		return true;
	}	

	function renameTag($uId, $oldName, $newName) {
		$tagservice =SemanticScuttle_Service_Factory::get('Tag');
		$newName = $tagservice->normalize($newName);

		$query = 'UPDATE `'. $this->getTableName() .'`';
		$query.= ' SET tag1="'.$newName.'"';
		$query.= ' WHERE tag1="'.$oldName.'"';
		$query.= ' AND uId="'.$uId.'"';
		$this->db->sql_query($query);

		$query = 'UPDATE `'. $this->getTableName() .'`';
		$query.= ' SET tag2="'.$newName.'"';
		$query.= ' WHERE tag2="'.$oldName.'"';
		$query.= ' AND uId="'.$uId.'"';
		$this->db->sql_query($query);


		// Update stats and cache
		$this->update($oldName, NULL, '=', $uId);
		$this->update($oldName, NULL, '>', $uId);
		$this->update($newName, NULL, '=', $uId);
		$this->update($newName, NULL, '>', $uId);

		return true;

	}

	function update($tag1, $tag2, $relationType, $uId) {
		$tsts =SemanticScuttle_Service_Factory::get('TagStat');
		$tsts->updateStat($tag1, $relationType, $uId);

		$tcs = SemanticScuttle_Service_Factory::get('TagCache');
		$tcs->deleteByUser($uId);
	}

	function deleteAll() {
		$query = 'TRUNCATE TABLE `'. $this->getTableName() .'`';
		$this->db->sql_query($query);

		$tsts =SemanticScuttle_Service_Factory::get('TagStat');
		$tsts->deleteAll();
	}

}
?>
