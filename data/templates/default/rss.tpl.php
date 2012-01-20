<?php
echo '<' . '?xml version="1.0" encoding="utf-8" ?' . ">\n";
?>
<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/"
     xmlns:atom="http://www.w3.org/2005/Atom"
>
<channel>
    <title><?php echo $feedtitle; ?></title>
    <link><?php echo htmlspecialchars($pagelink); ?></link>
    <atom:link rel="self" type="application/rss+xml" href="<?php echo htmlspecialchars($feedlink); ?>"/>
    <description><?php echo htmlspecialchars($feeddescription); ?></description>
    <pubDate><?php echo date('r'); ?></pubDate>
    <lastBuildDate><?php echo $feedlastupdate ?></lastBuildDate>
    <ttl>60</ttl>

<?php foreach($bookmarks as $bookmark): ?>
    <item>
        <title><?php echo htmlspecialchars($bookmark['title']); ?></title>
        <link><?php echo htmlspecialchars($bookmark['link']); ?></link>
        <guid><?php echo $bookmark['guid']; ?></guid>
        <description><?php echo htmlspecialchars($bookmark['description']); ?></description>
        <dc:creator><?php echo htmlspecialchars($bookmark['creator']); ?></dc:creator>
        <pubDate><?php echo $bookmark['pubdate']; ?></pubDate>
<?php foreach($bookmark['tags'] as $tag): ?>
        <category><?php echo htmlspecialchars($tag); ?></category>
<?php endforeach; ?>
    </item>
<?php endforeach; ?>
</channel>
</rss>
