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
 * SemanticScuttle tag caching service.
 *
 * This class infers on relation between tags by storing all
 * the including tags or synonymous tag.
 * For example, if the user creates: tag1>tag2>tag3, the system
 * can infer that tag is included into tag1.
 * Instead of computing this relation several times, it is saved
 * into this current table.
 * For synonymy, this table stores also the group of synonymous tags.
 * The table must be updated for each modification of
 * the relations between tags.
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Benjamin Huynh-Kim-Bang <mensonge@users.sourceforge.net>
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @author   Eric Dane <ericdane@users.sourceforge.net>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */
class SemanticScuttle_Service_TagCache extends SemanticScuttle_DbService
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

    protected function __construct($db)
    {
        $this->db =$db;
        $this->tablename = $GLOBALS['tableprefix'] .'tagscache';
    }

    function getChildren($tag1, $uId) {
        $tagservice =SemanticScuttle_Service_Factory::get('Tag');
        $tag1 = $tagservice->normalize($tag1);

        if($tag1 == '') return false;

        $query = "SELECT DISTINCT tag2 as 'tag'";
        $query.= " FROM `". $this->getTableName() ."`";
        $query.= " WHERE relationType = '>'";
        $query.= " AND tag1 = '" . $this->db->sql_escape($tag1) . "'";
        $query.= " AND uId = " . intval($uId);

        //die($query);
        if (! ($dbresult =& $this->db->sql_query($query)) ){
            message_die(GENERAL_ERROR, 'Could not get related tags', '', __LINE__, __FILE__, $query, $this->db);
            return false;
        }


        $rowset = $this->db->sql_fetchrowset($dbresult);
        $output = array();
        foreach($rowset as $row) {
            $output[] = $row['tag'];
        }

        $this->db->sql_freeresult($dbresult);
        return $output;
    }

    function addChild($tag1, $tag2, $uId) {
        $tagservice =SemanticScuttle_Service_Factory::get('Tag');
        $tag1 = $tagservice->normalize($tag1);
        $tag2 = $tagservice->normalize($tag2);

        if($tag1 == $tag2 || strlen($tag1) == 0 || strlen($tag2) == 0
        || ($this->existsChild($tag1, $tag2, $uId))) {
            return false;
        }

        $values = array('tag1' => $tag1, 'tag2' => $tag2, 'relationType'=> '>', 'uId'=> $uId);
        $query = 'INSERT INTO '. $this->getTableName() .' '. $this->db->sql_build_array('INSERT', $values);
        //die($query);
        if (!($dbresult =& $this->db->sql_query($query))) {
            $this->db->sql_transaction('rollback');
            message_die(GENERAL_ERROR, 'Could not add tag cache inference', '', __LINE__, __FILE__, $query, $this->db);
            return false;
        }
        $this->db->sql_transaction('commit');
    }

    function removeChild($tag1, $tag2, $uId) {
        if(($tag1 != '' && $tag1 == $tag2) ||
        ($tag1 == '' && $tag2 == '' && $uId == '')) {
            return false;
        }

        $query = 'DELETE FROM '. $this->getTableName();
        $query.= ' WHERE 1=1';
        $query.= strlen($tag1)>0 ? ' AND tag1 = \''. $this->db->sql_escape($tag1) . "'" : '';
        $query.= strlen($tag2)>0 ? ' AND tag2 = \''. $this->db->sql_escape($tag2) . "'" : '';
        $query.= ' AND relationType = ">"';
        $query.= strlen($uId)>0 ? ' AND uId = ' . intval($uId) : '';

        if (!($dbresult =& $this->db->sql_query($query))) {
            message_die(GENERAL_ERROR, 'Could not remove tag cache inference', '', __LINE__, __FILE__, $query, $this->db);
            return false;
        }
    }

    function removeChildren($tag1, $uId) {
        $this->removeChild($tag1, NULL, $uId);
    }

    function existsChild($tag1, $tag2, $uId) {
        $tagservice =SemanticScuttle_Service_Factory::get('Tag');
        $tag1 = $tagservice->normalize($tag1);
        $tag2 = $tagservice->normalize($tag2);

        $query = "SELECT tag1, tag2, relationType, uId FROM `". $this->getTableName() ."`";
        $query.= " WHERE tag1 = '" . $this->db->sql_escape($tag1) . "'";
        $query.= " AND tag2 = '" . $this->db->sql_escape($tag2) . "'";
        $query.= " AND relationType = '>'";
        $query.= " AND uId = " . intval($uId);

        //echo($query."<br>\n");

        $dbres = $this->db->sql_query($query);
        $rows = $this->db->sql_numrows($dbres);
        $this->db->sql_freeresult($dbres);
        return $rows > 0;
    }

    /*
     * Synonyms of a same concept are a group. A group has one main synonym called key
     * and a list of synonyms called values.
     */
    function addSynonym($tag1, $tag2, $uId) {

        if($tag1 == $tag2 || strlen($tag1) == 0 || strlen($tag2) == 0
        || ($this->existsSynonym($tag1, $tag2, $uId))) {
            return false;
        }

        $case1 = '0'; // not in DB
        if($this->_isSynonymKey($tag1, $uId)) {
            $case1 = 'key';
        } elseif($this->_isSynonymValue($tag1, $uId)) {
            $case1 = 'value';
        }

        $case2 = '0'; // not in DB
        if($this->_isSynonymKey($tag2, $uId)) {
            $case2 = 'key';
        } elseif($this->_isSynonymValue($tag2, $uId)) {
            $case2 = 'value';
        }
        $case = $case1.$case2;

        // all the possible cases
        switch ($case) {
            case 'keykey':
                $values = $this->_getSynonymValues($tag2, $uId);
                $this->removeSynonymGroup($tag2, $uId);
                foreach($values as $value) {
                    $this->addSynonym($tag1, $value['tag'], $uId);
                }
                $this->addSynonym($tag1, $tag2, $uId);
                break;

            case 'valuekey':
                $key = $this->_getSynonymKey($tag1, $uId);
                $this->addSynonym($key, $tag2, $uId);
                break;

            case 'keyvalue':
                $this->addSynonym($tag2, $tag1, $uId);
                break;
            case 'valuevalue':
                $key1 =  $this->_getSynonymKey($tag1, $uId);
                $key2 =  $this->_getSynonymKey($tag2, $uId);
                $this->addSynonym($key1, $key2, $uId);
                break;
            case '0value':
                $key = $this->_getSynonymKey($tag2, $uId);
                $this->addSynonym($key, $tag1, $uId);
                break;
            case 'value0':
                $this->addSynonym($tag2, $tag1, $uId);
                break;
            case '0key':
                $this->addSynonym($tag2, $tag1, $uId);
                break;
            default:
                $values = array('tag1' => $tag1, 'tag2' => $tag2, 'relationType'=> '=', 'uId'=> $uId);
                $query = 'INSERT INTO '. $this->getTableName() .' '. $this->db->sql_build_array('INSERT', $values);
                //die($query);
                if (!($dbresult =& $this->db->sql_query($query))) {
                    $this->db->sql_transaction('rollback');
                    message_die(GENERAL_ERROR, 'Could not add tag cache synonymy', '', __LINE__, __FILE__, $query, $this->db);
                    return false;
                }
                $this->db->sql_transaction('commit');
                break;
        }
    }

    function removeSynonymGroup($tag1, $uId) {
        $query = 'DELETE FROM '. $this->getTableName();
        $query.= ' WHERE 1=1';
        $query.= ' AND tag1 = \''. $this->db->sql_escape($tag1) . "'";
        $query.= ' AND relationType = "="';
        $query.= ' AND uId = ' . intval($uId);

        if (!($dbresult =& $this->db->sql_query($query))) {
            message_die(GENERAL_ERROR, 'Could not remove tag cache inference', '', __LINE__, __FILE__, $query, $this->db);
            return false;
        }
    }

    function _isSynonymKey($tag1, $uId) {
        $tagservice =SemanticScuttle_Service_Factory::get('Tag');
        $tag1 = $tagservice->normalize($tag1);

        $query = "SELECT tag1 FROM `". $this->getTableName() ."`";
        $query.= " WHERE tag1 = '" . $this->db->sql_escape($tag1) ."'";
        $query.= " AND relationType = '='";
        $query.= " AND uId = " . intval($uId);

        $dbres = $this->db->sql_query($query);
        $rows = $this->db->sql_numrows($dbres);
        $this->db->sql_freeresult($dbres);
        return $rows > 0;
    }

    function _isSynonymValue($tag2, $uId) {
        $tagservice =SemanticScuttle_Service_Factory::get('Tag');
        $tag2 = $tagservice->normalize($tag2);

        $query = "SELECT tag2 FROM `". $this->getTableName() ."`";
        $query.= " WHERE tag2 = '" . $this->db->sql_escape($tag2) . "'";
        $query.= " AND relationType = '='";
        $query.= " AND uId = " . intval($uId);

        $dbres = $this->db->sql_query($query);
        $rows = $this->db->sql_numrows($dbres);
        $this->db->sql_freeresult($dbres);
        return $rows > 0;
    }

    function getSynonyms($tag1, $uId) {
        $values = array();
        if($this->_isSynonymKey($tag1, $uId)) {
            $values = $this->_getSynonymValues($tag1, $uId);
        } elseif($this->_isSynonymValue($tag1, $uId)) {
            $key = $this->_getSynonymKey($tag1, $uId);
            $values = $this->_getSynonymValues($key, $uId, $tag1);
            $values[] = $key;
        }
        return $values;
    }

    function _getSynonymKey($tag2, $uId) {
        $tagservice =SemanticScuttle_Service_Factory::get('Tag');
        $tag2 = $tagservice->normalize($tag2);

        if($this->_isSynonymKey($tag2, $uId)) return $tag2;

        if($tag2 == '') return false;

        $query = "SELECT DISTINCT tag1 as 'tag'";
        $query.= " FROM `". $this->getTableName() ."`";
        $query.= " WHERE relationType = '='";
        $query.= " AND tag2 = '" . $this->db->sql_escape($tag2) . "'";
        $query.= " AND uId = " . intval($uId);

        //die($query);
        if (! ($dbresult =& $this->db->sql_query($query)) ){
            message_die(GENERAL_ERROR, 'Could not get related tags', '', __LINE__, __FILE__, $query, $this->db);
            return false;
        }

        $row = $this->db->sql_fetchrow($dbresult);
        $this->db->sql_freeresult($dbresult);
        return $row['tag'];
    }

    /*
     * Return values associated with a key.
     * $tagExcepted allows to hide a value.
     */
    function _getSynonymValues($tag1, $uId, $tagExcepted = NULL) {
        $tagservice =SemanticScuttle_Service_Factory::get('Tag');
        $tag1 = $tagservice->normalize($tag1);
        $tagExcepted = $tagservice->normalize($tagExcepted);

        if($tag1 == '') return false;

        $query = "SELECT DISTINCT tag2 as 'tag'";
        $query.= " FROM `". $this->getTableName() ."`";
        $query.= " WHERE relationType = '='";
        $query.= " AND tag1 = '" . $this->db->sql_escape($tag1) . "'";
        $query.= " AND uId = " . intval($uId);
        $query.= $tagExcepted!=''?" AND tag2!='" . $this->db->sql_escape($tagExcepted) . "'" : '';

        if (! ($dbresult =& $this->db->sql_query($query)) ){
            message_die(GENERAL_ERROR, 'Could not get related tags', '', __LINE__, __FILE__, $query, $this->db);
            return false;
        }

        $rowset = $this->db->sql_fetchrowset($dbresult);

        $output = array();
        foreach($rowset as $row) {
            $output[] = $row['tag'];
        }

        $this->db->sql_freeresult($dbresult);
        return $output;
    }

    function existsSynonym($tag1, $tag2, $uId) {
        if($this->_getSynonymKey($tag1, $uId) == $tag2 || $this->_getSynonymKey($tag2, $uId) == $tag1) {
            return true;
        } else {
            return false;
        }
    }


    function updateTag($tag1, $relationType, $otherTags, $uId) {
        if($relationType == '=') {
            if($this->getSynonyms($tag1, $uId)) {  // remove previous data avoiding unconstistency
                $this->removeSynonymGroup($tag1, $uId);
            }

            foreach($otherTags as $tag2) {
                $this->addSynonym($tag1, $tag2, $uId);
            }
        } elseif($relationType == '>') {
            if(count($this->getChildren($tag1, $uId))>0) { // remove previous data avoiding unconstistency
                $this->removeChildren($tag1);
            }

            foreach($otherTags as $tag2) {
                $this->addChild($tag1, $tag2, $uId);
            }
        }
    }

    function deleteByUser($uId) {
        $query = 'DELETE FROM '. $this->getTableName() .' WHERE uId = '. intval($uId);

        if (!($dbresult = & $this->db->sql_query($query))) {
            message_die(GENERAL_ERROR, 'Could not delete user tags cache', '', __LINE__, __FILE__, $query, $this->db);
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
