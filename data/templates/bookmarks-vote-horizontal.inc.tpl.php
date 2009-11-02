<?php
/**
 * Bookmark voting badge.
 * Shows the number of votes and buttons to vote for or
 * against a bookmark.
 * Expects a $row variable with bookmark data
 */
if (!$GLOBALS['enableVoting'] || $GLOBALS['votingMode'] != 2) {
    return;
}
if (!isset($row['hasVoted'])) {
    $classes = 'vote-horiz vote-horiz-inactive';
} else if (isset($row['vote']))  {
    $classes = 'vote-horiz '
        . ($row['vote'] == 1
           ? 'vote-horiz-for'
           : 'vote-horiz-against'
        );
} else {
    $classes = 'vote-horiz';
}
echo '<div class="' . $classes . '" id="bmv-' . $row['bId'] . '">';
echo 'Voting <span class="voting">' . $row['bVoting'] . '</span> ';

if (isset($row['hasVoted'])) {
    if ($row['vote'] != 1) {
        echo '<a class="vote-for" rel="nofollow" href="'
            . createVoteURL(true, $row['bId']) . '"'
            . ' onclick="javascript:vote(' . $row['bId'] . ',1); return false;"'
            . '>Vote for</a> ';
    } else {
        echo '<span class="vote-for-inactive">Vote for</span> ';
    }
    
    
    if ($row['vote'] != -1) {
        echo '<a class="vote-against" rel="nofollow" href="'
            . createVoteURL(false, $row['bId']) . '"'
            . ' onclick="vote(' . $row['bId'] . ',-1); return false;"'
            . '>Vote against</a>';
    } else {
        echo '<span class="vote-against-inactive">Vote against</span>';
    }
}
echo '</div>';
?>