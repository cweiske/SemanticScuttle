<?php
/***************************************************************************
Copyright (C) 2006 - 2007 Scuttle project
http://sourceforge.net/projects/scuttle/
http://scuttle.org/

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
***************************************************************************/


/////////////////
// WARNING!
// Comment the two lines of code below to make work the script.
// You have to put // at the beginning of lines to comment them.
// BEFORE upgrading, don't forget to make a BACKUP of your database.
// AFTER upgrading, don't forget to UN-COMMENT the lines back.
/////////////////

echo "Please edit the 'upgrade.php' file into SemanticScuttle/ to start upgrading";
exit();

/////////////////
// This part below will be executed once you comment the two lines above
/////////////////
require_once 'www-header.php';
$tagstatservice   = SemanticScuttle_Service_Factory :: get('TagStat');
?>

<h1>Upgrade</h1>
<h2>From Scuttle 0.7.2 to SemanticScuttle 0.87</h2>
<ul>
  <li>1/ Make a <b>backup</b> of your database</li>
  <li>2/ Create the missing tables
    <ul>
      <li>Open "tables.sql" (into your SemanticScuttle directory) with a text editor</li>
      <li>Copy to your database the last three tables : sc_tags2tags, sc_tagsstats, sc_commondescription</li>
    </ul>
  </li>
  <li>3/ Complete the upgrade by clicking on the following link : <a href="upgrade.php?action=upgrade">upgrade</a></li>
</ul>
<?php
if($_GET['action']=="upgrade") {
  // Update the stats
  $tagstatservice->updateAllStat();
  echo "Upgrade script executed: OK!<br/><br/>";
  echo "For security reason, don't forget to uncomment back the first lines into \"upgrade.php\"<br/><br/>";
  echo 'In case of problem during upgrade, please use our <a href="http://sourceforge.net/tracker/?group_id=211356&atid=1017431">sourceforge page</a> to inform us. Thank you.';
}
?>
