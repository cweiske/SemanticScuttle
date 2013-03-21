======================
SemanticScuttle 0.98.5
======================
A social bookmarking tool experimenting with new features
like structured tags or collaborative descriptions of tags.

- Home page: http://semanticscuttle.sourceforge.net/
- Project page: https://sourceforge.net/projects/semanticscuttle/
- Demo: http://semanticscuttle.sourceforge.net/demo/

Available under the GNU General Public License


Installation
============
See `INSTALL.rst`__


__ INSTALL.html


Upgrading from a previous version
=================================
See `UPGRADE.txt`__

__ UPGRADE.html


Public API
==========
SemanticScuttle supports most of the `del.icio.us API`__.
Almost all of the neat tools made for that system can be modified
to work with your SemanticScuttle installation. If you find a tool
that won't let you change the API address, ask the creator to add
this setting. You never know, they might just do it.

__ http://del.icio.us/doc/api



Links
-----
- `further documentation`__
- `support and help questions`__
- `development mailing list instructions`__
- `suggestions`_ for SemanticScuttle
- `bug reports`_
- `feature requests`_

__ http://semanticscuttle.wiki.sourceforge.net/
__ http://sourceforge.net/forum/forum.php?forum_id=759510
__ https://sourceforge.net/mailarchive/forum.php?forum_name=semanticscuttle-devel
.. _suggestions:  http://sourceforge.net/forum/forum.php?forum_id=759511
.. _bug reports: http://sourceforge.net/tracker/?group_id=211356&atid=1017430
.. _feature requests: https://sourceforge.net/tracker/?group_id=211356&atid=1017433




Known issues
============

Number of bookmarks always 0: "0 bookmark(s)"
---------------------------------------------
This issue occurs when debug mode is enabled.
Technically, this is because the database layers ``DEBUG_EXTRA`` gets
enabled through debug mode.
