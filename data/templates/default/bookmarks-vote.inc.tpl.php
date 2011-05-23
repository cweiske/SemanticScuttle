<?php
/**
 * Bookmark voting badge.
 * Shows the number of votes and buttons to vote for or
 * against a bookmark.
 * Expects a $row variable with bookmark data
 */
if (!$GLOBALS['enableVoting'] || $GLOBALS['votingMode'] != 1) {
    return;
}
if (isset($row['hasVoted']) && !$row['hasVoted']) {
    $classes = 'vote-badge vote-badge-inactive';
} else if (isset($row['vote']))  {
    $classes = 'vote-badge '
        . ($row['vote'] == 1
           ? 'vote-badge-for'
           : 'vote-badge-against'
        );
} else {
    $classes = 'vote-badge';
}
echo '<span class="' . $classes . '" id="bmv-' . $row['bId'] . '">';

if (isset($row['hasVoted']) && $row['vote'] != 1) {
    echo '<a class="vote-for" rel="nofollow" href="'
        . createVoteURL(true, $row['bId']) . '"'
        . ' onclick="javascript:vote(' . $row['bId'] . ',1); return false;"'
        . '>+</a>';
} else {
    echo '<span class="vote-for vote-for-inactive">+</span>';
}

echo '<span class="voting">' . $row['bVoting'] . '</span>';

if (isset($row['hasVoted']) && $row['vote'] != -1) {
    echo '<a class="vote-against" rel="nofollow" href="'
        . createVoteURL(false, $row['bId']) . '"'
        . ' onclick="vote(' . $row['bId'] . ',-1); return false;"'
        . '>-</a>';
} else {
    echo '<span class="vote-against vote-against-inactive">-</span>';
}
echo '</span>';
?>