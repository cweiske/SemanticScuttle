SemanticScuttle
https://sourceforge.net/projects/semanticscuttle/

Copyright (C) 2007 SemanticScuttle project
Available under the GNU General Public License

= INSTALL =
- Execute the SQL contained in tables.sql to create the necessary database tables. This file was written specifically for MySQL, so may need rewritten if you intend to use a different database system.
- Edit config.inc.php.example and save the changes as a new config.inc.php file in the same directory.
- Set the CHMOD permissions on the /cache/ subdirectory to 777

= UPGRADE =
See ./upgrade.txt

= Use API =
Scuttle supports most of the del.icio.us API [ http://del.icio.us/doc/api ]. Almost all of the neat tools made for that system can be modified to work with your installation instead. If you find a tool that won't let you change the API address, ask the creator to add this setting. You never know, they might just do it.

= TRANSLATE =
Scuttle is available in the folowing languages :

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

Translation is managed with gettext <includes/php-gettext>.

To provide additional translations :
- execute of of the scripts in <includes/php-gettext/bin/> ; for example to complete french (France) translation on a GNU/Linux, type
  ./gettexts.sh fr_FR
- then edit the file <locales/fr_FR/LC_MESSAGES/messages.po> with poedit (that will update <locales/fr_FR/LC_MESSAGES/messages.mo>)

= LINKS =
For further documentation, read http://semanticscuttle.wiki.sourceforge.net/

If you need help, post to http://sourceforge.net/forum/forum.php?forum_id=759510

Suggestions are welcome on http://sourceforge.net/forum/forum.php?forum_id=759511

Bug reports:
http://sourceforge.net/tracker/?group_id=211356&atid=1017430

Feature requests:

User-submitted patches:

Discussion forum:
