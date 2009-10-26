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

require_once 'prepare.php';

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'VoteTest::main');
}

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
     * Used to run this test class standalone
     *
     * @return void
     */
    public static function main()
    {
        require_once 'PHPUnit/TextUI/TestRunner.php';
        PHPUnit_TextUI_TestRunner::run(
            new PHPUnit_Framework_TestSuite('VoteTest')
        );
    }



    public function setUp()
    {
        //FIXME: create true new instance
        $this->vs = SemanticScuttle_Service_Factory::get('Vote');
        $this->vs->deleteAll();
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
     * Test hasVoted() when a vote has been cast for other bookmarks
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
        $this->assertFalse($this->vs->vote($bid, $uid, 1));

        $bid = $this->addBookmark();
        $this->assertTrue($this->vs->vote($bid, $uid, -1));
        $this->assertFalse($this->vs->vote($bid, $uid, 1));
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
    }

}//class VoteTest extends TestBase


if (PHPUnit_MAIN_METHOD == 'VoteTest::main') {
    VoteTest::main();
}
?>