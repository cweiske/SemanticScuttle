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
 * SemanticScuttle tag management service.
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Benjamin Huynh-Kim-Bang <mensonge@users.sourceforge.net>
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @author   Eric Dane <ericdane@users.sourceforge.net>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */
class SemanticScuttle_Service_Tag extends SemanticScuttle_DbService
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
        $this->tablename = $GLOBALS['tableprefix'] .'tags';
    }

    function getDescription($tag, $uId) {
        $query = 'SELECT tag, uId, tDescription';
        $query.= ' FROM '.$this->getTableName();
        $query.= ' WHERE tag = \''. $this->db->sql_escape($tag) . "'";
        $query.= ' AND uId = ' . intval($uId);

        if (!($dbresult = & $this->db->sql_query($query))) {
            message_die(GENERAL_ERROR, 'Could not get tag description', '', __LINE__, __FILE__, $query, $this->db);
            return false;
        }

        $row = $this->db->sql_fetchrow($dbresult);
        $this->db->sql_freeresult($dbresult);
        if ($row) {
            return $row;
        } else {
            return array('tDescription'=>'');
        }
    }

    function existsDescription($tag, $uId) {
            $query = 'SELECT tag, uId, tDescription';
        $query.= ' FROM '.$this->getTableName();
        $query.= ' WHERE tag = \'' . $this->db->sql_escape($tag) . "'";
        $query.= ' AND uId = "' . intval($uId) . '"';

        if (!($dbresult = & $this->db->sql_query($query))) {
            message_die(GENERAL_ERROR, 'Could not get tag description', '', __LINE__, __FILE__, $query, $this->db);
            return false;
        }

        $row = $this->db->sql_fetchrow($dbresult);
        $this->db->sql_freeresult($dbresult);
        if ($row) {
            return true;
        } else {
            return false;
        }
    }

    function getAllDescriptions($tag) {
        $query = 'SELECT tag, uId, tDescription';
        $query.= ' FROM '.$this->getTableName();
        $query.= ' WHERE tag = \''. $this->db->sql_escape($tag) . "'";

        if (!($dbresult = & $this->db->sql_query($query))) {
            message_die(GENERAL_ERROR, 'Could not get tag description', '', __LINE__, __FILE__, $query, $this->db);
            return false;
        }

        $rowset = $this->db->sql_fetchrowset($dbresult);
        $this->db->sql_freeresult($dbresult);
        return $rowset;
    }

    function updateDescription($tag, $uId, $desc) {
        if($this->existsDescription($tag, $uId)) {
            $query = 'UPDATE '.$this->getTableName();
            $query.= ' SET tDescription= \'' . $this->db->sql_escape($desc) . "'";
            $query.= ' WHERE tag=\'' . $this->db->sql_escape($tag) . "' AND uId=" . intval($uId);
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
        $newname = $this->normalize($newName);

        $query = 'UPDATE `'. $this->getTableName() .'`';
        $query.= ' SET tag=\'' . $this->db->sql_escape($newName) . "'";
        $query.= ' WHERE tag=\'' . $this->db->sql_escape($oldName) . "'";
        $query.= ' AND uId=' . intval($uId);
        $this->db->sql_query($query);
        return true;
    }

    /* normalize the input tags which could be a string or an array*/
    function normalize($tags) {
        //clean tags from strange characters
        $tags = str_replace(array('"', '\'', '/'), "_", $tags);

        //normalize
        if(!is_array($tags)) {
            $tags = utf8_strtolower(trim($tags));
        } else {
            $tags = array_filter($tags);//remove empty values
            for($i=0; $i<count($tags); $i++) {
                $tags[$i] = utf8_strtolower(trim($tags[$i]));
            }
        }
        return $tags;
    }

    function deleteAll() {
        $query = 'TRUNCATE TABLE `'. $this->getTableName() .'`';
        $this->db->sql_query($query);
    }

}
?>
