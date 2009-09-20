SemanticScuttle
===============
A social bookmarking tool experimenting with new features
like structured tags or collaborative descriptions of tags.

https://sourceforge.net/projects/semanticscuttle/

Available under the GNU General Public License


Installation
------------
See INSTALL.txt


Upgrading from a previous version
---------------------------------
See UPGRADE.txt


Public API
----------
Scuttle supports most of the del.icio.us API [1].
Almost all of the neat tools made for that system can be modified
to work with your SemanticScuttle installation. If you find a tool
that won't let you change the API address, ask the creator to add
this setting. You never know, they might just do it.

[1] http://del.icio.us/doc/api


Translations
------------
Scuttle is available in the following languages :

English     en-GB   100% (Default)
Chinese     zh-CN   86%
Danish      dk-DK   100%
Dutch       nl-NL   68%
French      fr-FR   100%
German      de-DE   100%
Hindi       hi-IN   100%
Italian     it-IT   89%
Japanese    ja-JP   100%
Lithuanian  lt-LT   100%
Portuguese  pt-BR   100%
Spanish     es-ES   94%

Translations are managed with gettext <includes/php-gettext>.

To provide additional translations:
- execute of of the scripts in <includes/php-gettext/bin/>
  for example to complete french (France) translation on a
  GNU/Linux system, type
  ./gettexts.sh fr_FR
- then edit the file <locales/fr_FR/LC_MESSAGES/messages.po>
  with poedit
  (that will update <locales/fr_FR/LC_MESSAGES/messages.mo>)


Links
-----
http://semanticscuttle.wiki.sourceforge.net/
 - further documentation

http://sourceforge.net/forum/forum.php?forum_id=759510
 - support and help questions

https://sourceforge.net/mailarchive/forum.php?forum_name=semanticscuttle-devel
 - development mailing list instructions

http://sourceforge.net/forum/forum.php?forum_id=759511
 - suggestions for SemanticScuttle

http://sourceforge.net/tracker/?group_id=211356&atid=1017430
 - bug reports

https://sourceforge.net/tracker/?group_id=211356&atid=1017433
 - feature requests

https://sourceforge.net/tracker/?group_id=211356&atid=1017432
 - patches
