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
 * SemanticScuttle voting system.
 *
 * Each registered user in SemanticScuttle may vote
 * for or against a bookmark, counting +1 or -1.
 * The sum of all votes determines the "voting" of a bookmark.
 * Every user is equal in voting. Each vote is tied to a user.
 *
 * @internal
 * Votes are saved in a separate "votes" table.
 * Additionally to that, the voting and number of votes
 * of a bookmark is also stored in the bookmarks table.
 *  This is done to make sure lookups are really fast, since
 * every bookmark in a list shows its voting.
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author Christian Weiske <cweiske@cweiske.de>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */
class SemanticScuttle_Service_Vote extends SemanticScuttle_DbService
{
    /**
     * Returns the single service instance
     *
     * @param sql_db $db Database object
     *
     * @return SemanticScuttle_Service_Vote
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
     * Create a new instance.
     *
     * @param sql_db $db Database object
     */
	protected function __construct($db)
    {
		$this->db = $db;
		$this->tablename  = $GLOBALS['tableprefix'] . 'votes';
	}



    /**
     * Returns the sum of votes for the given bookmark.
     *
     * @internal
     * Uses the "votes" table to retrieve the votes, which
     * has high costs. It is more efficient to get the sum of
     * all votes for a bookmark from the bookmarks table,
     * field bVoting.
     *
     * @param integer $bookmark Bookmark ID
     *
     * @return integer Vote (can be positive, 0 or negative)
     */
    public function getVoting($bookmark)
    {
        $query = 'SELECT SUM(vote) as sum FROM ' . $this->getTableName()
            . ' WHERE bid = "' . $this->db->sql_escape($bookmark) . '"';

        if (!($dbres = $this->db->sql_query_limit($query, 1, 0))) {
            message_die(
                GENERAL_ERROR, 'Could not get voting',
                '', __LINE__, __FILE__, $query, $this->db
            );
            //FIXME: throw exception
        }

        $row = $this->db->sql_fetchrow($dbres);
        $this->db->sql_freeresult($dbres);

        return (int)$row['sum'];
    }//public function getVoting(..)



    /**
     * Returns the number of users that voted for or
     * against the given bookmark.
     *
     * @internal
     * This method uses the votes table to calculate the
     * number of votes for a bookmark. In normal life, it
     * is more efficient to use the "bVotes" field in the
     * bookmarks table.
     *
     * @param integer $bookmark Bookmark ID
     *
     * @return integer Number of votes
     */
    public function getVotes($bookmark)
    {
        $query = 'SELECT COUNT(vote) as count FROM '
            . $this->getTableName()
            . ' WHERE bid = "' . $this->db->sql_escape($bookmark) . '"';

        if (!($dbres = $this->db->sql_query_limit($query, 1, 0))) {
            message_die(
                GENERAL_ERROR, 'Could not get vote count',
                '', __LINE__, __FILE__, $query, $this->db
            );
            //FIXME: throw exception
        }

        $row = $this->db->sql_fetchrow($dbres);
        $this->db->sql_freeresult($dbres);

        return (int)$row['count'];
    }



    /**
     * Returns if the user has already voted for
     * the given bookmark.
     *
     * @param integer $bookmark Bookmark ID
     * @param integer $user     User ID
     *
     * @return boolean True if the user has already voted
     */
    public function hasVoted($bookmark, $user)
    {
        $query = 'SELECT COUNT(vote) as count FROM '
            . $this->getTableName()
            . ' WHERE'
            . ' bid = "' . $this->db->sql_escape($bookmark) . '"'
            . ' AND uid = "' . $this->db->sql_escape($user) . '"';

        if (!($dbres = $this->db->sql_query_limit($query, 1, 0))) {
            message_die(
                GENERAL_ERROR, 'Could not get vote count',
                '', __LINE__, __FILE__, $query, $this->db
            );
            //FIXME: throw exception
        }

        $row = $this->db->sql_fetchrow($dbres);
        $this->db->sql_freeresult($dbres);

        return (int)$row['count'] == 1;
    }



    /**
     * Returns the actual vote the given user
     * gave the bookmark.
     *
     * @param integer $bookmark Bookmark ID
     * @param integer $user     User ID
     *
     * @return integer Either 1 or -1, null when not voted.
     */
    public function getVote($bookmark, $user)
    {
        $query = 'SELECT vote FROM ' . $this->getTableName()
            . ' WHERE'
            . ' bid = "' . $this->db->sql_escape($bookmark) . '"'
            . ' AND uid = "' . $this->db->sql_escape($user) . '"';

        if (!($dbres = $this->db->sql_query_limit($query, 1, 0))) {
            message_die(
                GENERAL_ERROR, 'Could not get vote count',
                '', __LINE__, __FILE__, $query, $this->db
            );
            //FIXME: throw exception
        }

        $row = $this->db->sql_fetchrow($dbres);
        $this->db->sql_freeresult($dbres);

        if (!$row) {
            return null;
        }

        return $row['vote'];
    }



    /**
     * Let a user vote for the bookmark.
     *
     * @internal
     * We check if voting is enabled or not,
     * and if the user has already voted.
     * It is up to the calling code to make sure
     * the user is authorized to vote.
     *
     * @param integer $bookmark Bookmark ID
     * @param integer $user     User ID
     * @param integer $vote     1 or -1
     *
     * @return boolean True if the vote was saved,
     *                 false if there was a problem
     *                 (i.e. already voted)
     */
    public function vote($bookmark, $user, $vote = 1)
    {
        if ($GLOBALS['enableVoting'] == false) {
            return false;
        }

        if ($vote != -1 && $vote != 1) {
            return false;
        }

        if ($this->hasVoted($bookmark, $user)) {
            $this->removeVote($bookmark, $user);
        }

        $res = $this->db->sql_query(
            'INSERT INTO ' . $this->getTableName()
            . ' SET'
            . ' bid = ' . (int)$bookmark
            . ',uid = ' . (int)$user
            . ',vote = ' . (int)$vote
        );
        //FIXME: check for sql error
        $this->db->sql_freeresult($res);

        //update bookmark table
        $bm  = SemanticScuttle_Service_Factory::get('Bookmark');
        $res = $this->db->sql_query(
            $sql='UPDATE ' . $bm->getTableName()
            . ' SET bVoting = bVoting + ' . (int)$vote
            . ' , bVotes = bVotes + 1'
            . ' WHERE bId = ' . (int)$bookmark
        );
        $this->db->sql_freeresult($res);

        return true;
    }



    /**
     * Removes a vote from the database
     *
     * @param integer $bookmark Bookmark ID
     * @param integer $user     User ID
     *
     * @return boolean True if all went well, false if not
     */
    protected function removeVote($bookmark, $user)
    {
        $vote = $this->getVote($bookmark, $user);
        if ($vote === null) {
            return false;
        }

        //remove from votes table
        $query = 'DELETE FROM ' . $this->getTableName()
            . ' WHERE bId = ' . (int)$bookmark
            . ' AND uId = ' . (int)$user;
        $this->db->sql_query($query);

        //change voting sum in bookmarks table
        $bm  = SemanticScuttle_Service_Factory::get('Bookmark');
        $res = $this->db->sql_query(
            $sql='UPDATE ' . $bm->getTableName()
            . ' SET bVoting = bVoting - ' . (int)$vote
            . ' , bVotes = bVotes - 1'
            . ' WHERE bId = ' . (int)$bookmark
        );
        $this->db->sql_freeresult($res);

        return true;
    }



    /**
     * Re-calculates all votings for all bookmarks
     * and updates the voting values in the bookmarks
     * table.
     * This is mainly meant to be an administrative method
     * to fix a broken database.
     *
     * @return void
     */
    public function rewriteVotings()
    {
        $bm    = SemanticScuttle_Service_Factory::get('Bookmark');
        $query = 'UPDATE ' . $bm->getTableName() . ' as B SET bVoting = '
            . '(SELECT SUM(vote) FROM ' . $this->getTableName() . ' as V'
            . ' WHERE V.bId = B.bId GROUP BY bid)'
            . ', bVotes = '
            . '(SELECT COUNT(vote) FROM ' . $this->getTableName() . ' as V'
            . ' WHERE V.bId = B.bId GROUP BY bid)';
        $this->db->sql_query($query);
    }



    /**
     * Delete all votes from the database table.
     * Used in unit tests.
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