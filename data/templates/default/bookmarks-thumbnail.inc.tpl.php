<?php
/**
 * Bookmark thumbnail image
 * Shows the website thumbnail for the bookmark.
 *
 * Expects a $row variable with bookmark data.
 */

$thumbnailer = SemanticScuttle_Service_Factory::get('Thumbnails')->getThumbnailer();
$imgUrl      = $thumbnailer->getThumbnailUrl($address, 120, 90);
if ($imgUrl !== false) {
    echo '<a href="' . htmlspecialchars($address) . '">'
        . '<img class="thumbnail" width="120" height="90" src="'
        . htmlspecialchars($imgUrl).
        '" />'
        . '</a>';
}
?>