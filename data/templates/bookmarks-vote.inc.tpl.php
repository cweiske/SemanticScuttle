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
if (isset($row['hasVoted']) && !$row['hasVoted']) {
    $classes = 'vote-badge vote-badge-inactive';
} else {
    $classes = 'vote-badge';
}
echo '<span class="' . $classes . '">';

if (isset($row['hasVoted']) && !$row['hasVoted']) {
    echo '<a class="vote-for" href="'
        . createVoteURL(true, $row['bId']) . '">+</a>';
} else {
    echo '<span class="vote-for-inactive">+</span>';
}

echo '<span class="voting">' . $row['bVoting'] . '</span>';

if (isset($row['hasVoted']) && !$row['hasVoted']) {
    echo '<a class="vote-against" href="'
        . createVoteURL(false, $row['bId']) . '">-</a>';
} else {
    echo '<span class="vote-against-inactive">-</span>';
}
echo '</span>';
?>