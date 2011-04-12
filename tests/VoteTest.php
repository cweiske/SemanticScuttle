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
 * Unit tests for the SemanticScuttle voting system.
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */
class VoteTest extends TestBase
{
    /**
     * Vote service instance to test.
     *
     * @var SemanticScuttle_Service_Vote
     */
    protected $vs = null;

    /**
     * Bookmark service instance.
     *
     * @var SemanticScuttle_Service_Bookmark
     */
    protected $bs = null;



    public function setUp()
    {
        $GLOBALS['enableVoting'] = true;
        //FIXME: create true new instance
        $this->vs = SemanticScuttle_Service_Factory::get('Vote');
        $this->vs->deleteAll();

        $this->bs = SemanticScuttle_Service_Factory::get('Bookmark');
    }



    /**
     * Test getVoting() when no votes have been cast.
     *
     * @return void
     */
    public function testGetVotingZero()
    {
        $bid = $this->addBookmark();
        $this->assertEquals(0, $this->vs->getVoting($bid));

        $bm = $this->bs->getBookmark($bid);
        $this->assertEquals(0, $bm['bVoting']);
        $this->assertEquals(0, $bm['bVotes']);
    }



    /**
     * Test getVoting() when one positive vote has been cast.
     *
     * @return void
     */
    public function testGetVotingOne()
    {
        $bid = $this->addBookmark();
        $this->vs->vote($bid, 1, 1);
        $this->assertEquals(1, $this->vs->getVoting($bid));

        $bm = $this->bs->getBookmark($bid);
        $this->assertEquals(1, $bm['bVoting']);
        $this->assertEquals(1, $bm['bVotes']);
    }



    /**
     * Test getVoting() when one nevative vote has been cast.
     *
     * @return void
     */
    public function testGetVotingMinusOne()
    {
        $bid = $this->addBookmark();
        $this->vs->vote($bid, 1, -1);
        $this->assertEquals(-1, $this->vs->getVoting($bid));

        $bm = $this->bs->getBookmark($bid);
        $this->assertEquals(-1, $bm['bVoting']);
        $this->assertEquals(1, $bm['bVotes']);
    }



    /**
     * Test getVoting() when several votes have been cast.
     *
     * @return void
     */
    public function testGetVotingSum()
    {
        $bid = $this->addBookmark();
        $this->vs->vote($bid, 1, 1);
        $this->vs->vote($bid, 2, -1);
        $this->vs->vote($bid, 3, 1);
        $this->vs->vote($bid, 4, 1);
        $this->assertEquals(2, $this->vs->getVoting($bid));

        $bm = $this->bs->getBookmark($bid);
        $this->assertEquals(2, $bm['bVoting']);
        $this->assertEquals(4, $bm['bVotes']);
    }



    /**
     * Test getVotes() when no vote has been cast.
     *
     * @return void
     */
    public function testGetVotesZero()
    {
        $bid = $this->addBookmark();
        $this->assertEquals(0, $this->vs->getVotes($bid));

        $bm = $this->bs->getBookmark($bid);
        $this->assertEquals(0, $bm['bVoting']);
        $this->assertEquals(0, $bm['bVotes']);
    }



    /**
     * Test getVotes() when one vote has been cast.
     *
     * @return void
     */
    public function testGetVotesOne()
    {
        $bid = $this->addBookmark();
        $this->vs->vote($bid, 1, 1);
        $this->assertEquals(1, $this->vs->getVotes($bid));

        $bm = $this->bs->getBookmark($bid);
        $this->assertEquals(1, $bm['bVoting']);
        $this->assertEquals(1, $bm['bVotes']);
    }



    /**
     * Test getVotes() when several votes have been cast.
     *
     * @return void
     */
    public function testGetVotesMultiple()
    {
        $bid = $this->addBookmark();
        $this->vs->vote($bid, 1, 1);
        $this->vs->vote($bid, 2, -1);
        $this->vs->vote($bid, 3, 1);
        $this->vs->vote($bid, 4, 1);
        $this->assertEquals(4, $this->vs->getVotes($bid));

        $bm = $this->bs->getBookmark($bid);
        $this->assertEquals(2, $bm['bVoting']);
        $this->assertEquals(4, $bm['bVotes']);
    }



    /**
     * Test hasVoted() when a no vote has been cast
     *
     * @return void
     */
    public function testHasVotedFalse()
    {
        $uid = 1;
        $bid = $this->addBookmark();
        $this->assertFalse($this->vs->hasVoted($bid, $uid));
    }



    /**
     * Test hasVoted() when a vote has been cast
     *
     * @return void
     */
    public function testHasVotedTrue()
    {
        $uid = 1;
        $bid = $this->addBookmark();
        $this->vs->vote($bid, $uid, 1);
        $this->assertTrue($this->vs->hasVoted($bid, $uid));
    }



    /**
     * Test hasVoted() when a vote has been cast for other bookmarks.
     * Also verify that the bookmark voting did not change for
     * the other bookmarks.
     *
     * @return void
     */
    public function testHasVotedFalseOthers()
    {
        $uid = 1;
        $bid = $this->addBookmark();
        $bid2 = $this->addBookmark();
        $bid3 = $this->addBookmark();

        $this->vs->vote($bid, $uid, 1);
        $this->vs->vote($bid3, $uid, 1);

        $this->assertFalse($this->vs->hasVoted($bid2, $uid));

        $bm2 = $this->bs->getBookmark($bid2);
        $this->assertEquals(0, $bm2['bVoting']);
        $this->assertEquals(0, $bm2['bVotes']);
    }



    /**
     * Test getVote() when no vote has been cast.
     *
     * @return void
     */
    public function testGetVoteNone()
    {
        $uid = 1;
        $bid = $this->addBookmark();
        $this->assertNull($this->vs->getVote($bid, $uid));
    }



    /**
     * Test getVote() when a positive vote has been cast.
     *
     * @return void
     */
    public function testGetVoteOne()
    {
        $uid = 1;
        $bid = $this->addBookmark();
        $this->vs->vote($bid, $uid, 1);
        $this->assertEquals(1, $this->vs->getVote($bid, $uid));
    }



    /**
     * Test getVote() when a negavitve vote has been cast.
     *
     * @return void
     */
    public function testGetVoteMinusOne()
    {
        $uid = 1;
        $bid = $this->addBookmark();
        $this->vs->vote($bid, $uid, -1);
        $this->assertEquals(-1, $this->vs->getVote($bid, $uid));
    }



    /**
     * Test vote() when voting is deactivated
     *
     * @return void
     */
    public function testVoteVotingDeactivated()
    {
        $GLOBALS['enableVoting'] = false;

        $uid = 1;
        $bid = $this->addBookmark();
        $this->assertFalse($this->vs->vote($bid, $uid, 1));
    }



    /**
     * Test vote() with wrong vote parameter
     *
     * @return void
     */
    public function testVoteWrongVoteParam()
    {
        $uid = 1;
        $bid = $this->addBookmark();
        $this->assertFalse($this->vs->vote($bid, $uid, 2));
        $this->assertFalse($this->vs->vote($bid, $uid, 0));
        $this->assertFalse($this->vs->vote($bid, $uid, 1.5));
        $this->assertFalse($this->vs->vote($bid, $uid, -1.1));
        $this->assertFalse($this->vs->vote($bid, $uid, 'yes'));
        $this->assertFalse($this->vs->vote($bid, $uid, 'no'));
    }



    /**
     * Test vote() when the user already has voted
     *
     * @return void
     */
    public function testVoteHasVoted()
    {
        $uid = 1;
        $bid = $this->addBookmark();
        $this->assertTrue($this->vs->vote($bid, $uid, 1));
        $this->assertTrue($this->vs->vote($bid, $uid, 1));

        $bm = $this->bs->getBookmark($bid);
        $this->assertEquals(1, $bm['bVoting']);
        $this->assertEquals(1, $bm['bVotes']);

        $bid = $this->addBookmark();
        $this->assertTrue($this->vs->vote($bid, $uid, -1));
        $this->assertTrue($this->vs->vote($bid, $uid, 1));

        $bm = $this->bs->getBookmark($bid);
        $this->assertEquals(1, $bm['bVoting']);
        $this->assertEquals(1, $bm['bVotes']);
    }



    /**
     * Test vote() with positive vote
     *
     * @return void
     */
    public function testVotePositive()
    {
        $uid = 1;
        $bid = $this->addBookmark();
        $this->assertTrue($this->vs->vote($bid, $uid, 1));
        $this->assertEquals(1, $this->vs->getVote($bid, $uid));

        $bm = $this->bs->getBookmark($bid);
        $this->assertEquals(1, $bm['bVoting']);
        $this->assertEquals(1, $bm['bVotes']);
    }



    /**
     * Test vote() with negative vote
     *
     * @return void
     */
    public function testVoteNegative()
    {
        $uid = 1;
        $bid = $this->addBookmark();
        $this->assertTrue($this->vs->vote($bid, $uid, -1));
        $this->assertEquals(-1, $this->vs->getVote($bid, $uid));

        $bm = $this->bs->getBookmark($bid);
        $this->assertEquals(-1, $bm['bVoting']);
        $this->assertEquals(1, $bm['bVotes']);
    }



    /**
     * Verify that changing the vote from positive to negative
     * works.
     *
     * @return void
     */
    public function testVoteChangePosNeg()
    {
        $uid = 1;
        $bid = $this->addBookmark();

        $this->assertTrue($this->vs->vote($bid, $uid, 1));
        $this->assertEquals(1, $this->vs->getVote($bid, $uid));
        $this->assertEquals(1, $this->vs->getVotes($bid));

        $b = $this->bs->getBookmark($bid);
        $this->assertEquals(1, $b['bVoting']);
        $this->assertEquals(1, $b['bVotes']);

        //change vote
        $this->assertTrue($this->vs->vote($bid, $uid, -1));
        $this->assertEquals(-1, $this->vs->getVote($bid, $uid));
        $this->assertEquals(1, $this->vs->getVotes($bid));

        $b = $this->bs->getBookmark($bid);
        $this->assertEquals(-1, $b['bVoting']);
        $this->assertEquals(1, $b['bVotes']);
    }



    /**
     * Verify that changing the vote from negative to positive
     * works.
     *
     * @return void
     */
    public function testVoteChangeNegPos()
    {
        $uid = 1;
        $bid = $this->addBookmark();

        $this->assertTrue($this->vs->vote($bid, $uid, -1));
        $this->assertEquals(-1, $this->vs->getVote($bid, $uid));
        $this->assertEquals(1, $this->vs->getVotes($bid));

        $b = $this->bs->getBookmark($bid);
        $this->assertEquals(-1, $b['bVoting']);
        $this->assertEquals(1, $b['bVotes']);

        //change vote
        $this->assertTrue($this->vs->vote($bid, $uid, 1));
        $this->assertEquals(1, $this->vs->getVote($bid, $uid));
        $this->assertEquals(1, $this->vs->getVotes($bid));

        $b = $this->bs->getBookmark($bid);
        $this->assertEquals(1, $b['bVoting']);
        $this->assertEquals(1, $b['bVotes']);
    }



    /**
     * Verify that changing the vote from postitive to positive
     * has no strange effects
     *
     * @return void
     */
    public function testVoteChangePosPos()
    {
        $uid = 1;
        $bid = $this->addBookmark();

        $this->assertTrue($this->vs->vote($bid, $uid, 1));
        $this->assertEquals(1, $this->vs->getVote($bid, $uid));
        $this->assertEquals(1, $this->vs->getVotes($bid));

        $b = $this->bs->getBookmark($bid);
        $this->assertEquals(1, $b['bVoting']);
        $this->assertEquals(1, $b['bVotes']);

        //change vote
        $this->assertTrue($this->vs->vote($bid, $uid, 1));
        $this->assertEquals(1, $this->vs->getVote($bid, $uid));
        $this->assertEquals(1, $this->vs->getVotes($bid));

        $b = $this->bs->getBookmark($bid);
        $this->assertEquals(1, $b['bVoting']);
        $this->assertEquals(1, $b['bVotes']);
    }



    /**
     * Verify that changing the vote from negative to negative
     * has no strange effects
     *
     * @return void
     */
    public function testVoteChangeNegNeg()
    {
        $uid = 1;
        $bid = $this->addBookmark();

        $this->assertTrue($this->vs->vote($bid, $uid, -1));
        $this->assertEquals(-1, $this->vs->getVote($bid, $uid));
        $this->assertEquals(1, $this->vs->getVotes($bid));

        $b = $this->bs->getBookmark($bid);
        $this->assertEquals(-1, $b['bVoting']);
        $this->assertEquals(1, $b['bVotes']);

        //change vote to same value
        $this->assertTrue($this->vs->vote($bid, $uid, -1));
        $this->assertEquals(-1, $this->vs->getVote($bid, $uid));
        $this->assertEquals(1, $this->vs->getVotes($bid));

        $b = $this->bs->getBookmark($bid);
        $this->assertEquals(-1, $b['bVoting']);
        $this->assertEquals(1, $b['bVotes']);
    }



    /**
     * Test that rewriting votings does work
     *
     * @return void
     */
    public function testRewriteVotings()
    {
        $uid = 1;
        $bid = $this->addBookmark();
        $this->assertTrue($this->vs->vote($bid, $uid, 1));

        $bm = $this->bs->getBookmark($bid);
        $this->assertEquals(1, $bm['bVoting']);
        $this->assertEquals(1, $bm['bVotes']);

        $this->vs->deleteAll();
        //we assume that $vs->deleteAll() does *not* reset
        //voting in bookmarks table
        $bm = $this->bs->getBookmark($bid);
        $this->assertEquals(1, $bm['bVoting']);
        $this->assertEquals(1, $bm['bVotes']);

        $this->vs->rewriteVotings();
        $bm = $this->bs->getBookmark($bid);
        //now it should be reset to 0
        $this->assertEquals(0, $bm['bVoting']);
        $this->assertEquals(0, $bm['bVotes']);
    }

}//class VoteTest extends TestBase
?>