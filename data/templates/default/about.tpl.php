<?php
$this->includeTemplate($GLOBALS['top_include']);
?>

<ul>
<li><?php echo T_('<strong>Store</strong> all your favourite links in one place, accessible from anywhere.'); ?></li>
<li><?php echo T_('<strong>Share</strong> your bookmarks with everyone, with friends on your watchlist or just keep them private.') ;?></li>
<li><?php echo T_('<strong>Tag</strong> your bookmarks with as many labels as you want, instead of wrestling with folders.'); ?></li>
<li><?php echo sprintf('<strong><a href="'.createURL('register').'">'.T_('Register now').'</a> </strong>'.T_(' to start using %s!'), $GLOBALS['sitename']); ?></li>
</ul>

<h3><?php echo T_('Geek Stuff'); ?></h3>
<ul>
<li><a href="http://sourceforge.net/projects/semanticscuttle/">Semantic Scuttle</a> <?php echo T_('is licensed under the ');?> <a href="http://www.gnu.org/copyleft/gpl.html"><acronym title="GNU\'s Not Unix">GNU</acronym> General Public License</a> (<?php echo T_('you can freely host it on your own web server.'); ?>)</li>
<li><?php echo sprintf(T_('%1$s supports most of the <a href="http://www.delicious.com/help/api">del.icio.us <abbr title="Application Programming Interface">API</abbr></a>. Almost all of the neat tools made for that system can be modified to work with %1$s instead. If you find a tool that won\'t let you change the API address, ask the creator to add this setting. You never know, they might just do it.'), $GLOBALS['sitename']); ?></li>



<?php if(!is_null($currentUser) && $currentUser->isAdmin()): ?>
<li>SemanticScuttle v0.98.5</li>
<?php endif ?>
</ul>

<h3><?php echo T_('Tips'); ?></h3>
<ul>
<li><?php echo T_('Add search plugin into your browser:'); ?> <a href="#" onclick="window.external.AddSearchProvider('<?php echo ROOT?>api/opensearch.php');">opensearch</a></li>
<li><?php echo T_('The secret tag "system:unfiled" allows you to find bookmarks without tags.'); ?></li>
<li><?php echo T_('The secret tag "system:imported" allows you to find imported bookmarks.'); ?></li>
</ul>


<?php
$this->includeTemplate($GLOBALS['bottom_include']);
?>
