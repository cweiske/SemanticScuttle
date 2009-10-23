<?php

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
 * Additionally to that, the voting of a bookmark is also
 * stored in the bookmarks table. This is done to make
 * sure lookups are really fast, since every bookmarks
 * in a list shows its voting.
 *
 * @author Christian Weiske <cweiske@cweiske.de>
 */
class SemanticScuttle_Service_Vote extends SemanticScuttle_Service
{
    /**
     * Database object
     *
     * @var sql_db
     */
    protected $db;



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
     * @param integer $bookmark Bookmark ID
     *
     * @return integer Vote (can be positive, 0 or negative)
     */
    public function getVoting($bookmark)
    {
        //FIXME
    }



    /**
     * Returns the number of users that voted for or
     * against the given bookmark.
     *
     * @param integer $bookmark Bookmark ID
     *
     * @return integer Number of votes
     */
    public function getVotes($bookmark)
    {
        //FIXME
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
        //FIXME
    }



    /**
     * Returns the actual vote the given user
     * gave the bookmark.
     *
     * @param integer $bookmark Bookmark ID
     * @param integer $user     User ID
     *
     * @return integer Either 1 or -1.
     */
    public function getVote($bookmark, $user)
    {
        //FIXME
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
        //FIXME: check if voting is enabled (global conf var)

        if ($this->hasVoted($bookmark, $user)) {
            return false;
        }

        if ($vote != -1 && $vote != 1) {
            return false;
        }

        $dbresult = $this->db->sql_query(
            'INSERT INTO ' . $this->getTableName()
            . ' SET'
            . ' bid = ' . (int)$bookmark
            . ',uid = ' . (int)$user
            . ',vote = ' . (int)$vote
        );
        //FIXME: check for sql error
        $this->db->sql_freeresult();
        //FIXME: update bookmarks table
    }



    /**
     * Re-calculates all votings for all bookmarks
     * and updates the voting values in the bookmarks
     * table.
     *
     * @return void
     */
    public function rewriteVotings()
    {
        //FIXME
    }


}

?>