How to release a new version of SemanticScuttle
===============================================

0. Run unit tests and verify that all of them pass
1. Update ``doc/ChangeLog``
2. Update ``doc/UPGRADE.txt``
3. Update version in ``data/templates/default/about.tpl.php``,
   ``build.xml`` and ``doc/README.rst``
4. Create a release zip file via the build script:
   Just type "``phing``".
5. Make a test installation from your zip file with a fresh
   database. Register a user, add bookmarks etc.
6. When all is fine, it's time to release.
   The build script takes care for most of the
   tasks.
   Run "``phing release``", and it will upload the release to
   sourceforge and create a svn tag.
7. Write announcement mail to the SemanticScuttle mailing list
   semanticscuttle-devel@lists.sourceforge.net
8. Announce the new release in the sourceforge project news
   https://sourceforge.net/apps/trac/sourceforge/wiki/News

