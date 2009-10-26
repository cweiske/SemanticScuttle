<?php
/**
 * Bookmark voting badge.
 * Shows the number of votes and buttons to vote for or
 * against a bookmark.
 * Expects a $row variable with bookmark data
 */
if (!$GLOBALS['enableVoting']) {
    return;
}
echo '<span class="vote-badge">';
if (!$row['hasVoted']) {
    echo '<a class="vote-for" href="'
        . createVoteURL(true, $row['bId']) . '">+</a>';
} else {
    echo '<span class="vote-against-i">+</span>';
}
echo '<span class="voting">' . $row['bVoting'] . '</span>';
if (!$row['hasVoted']) {
    echo '<a class="vote-against" href="'
        . createVoteURL(false, $row['bId']) . '">-</a>';
} else {
    echo '<span class="vote-against-i">-</span>';
}
echo '</span>';
?>