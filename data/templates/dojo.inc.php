<?php
/***************************************************************************
 Copyright (C) 2005 - 2006 Scuttle project
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

?>

<?php if (isset($loadjs)) :?>
<script type="text/javascript"
	src="http://ajax.googleapis.com/ajax/libs/dojo/1.2/dojo/dojo.xd.js"
	djConfig="parseOnLoad:true, isDebug:<?php echo DEBUG_MODE?'true':'false' ?>, usePlainJson:true, baseUrl: '<?php echo ROOT ?>', modulePaths: {'js': 'js'}"></script>
 
<script type="text/javascript">
dojo.require("dojo.parser");
dojo.require("dojo.data.ItemFileReadStore");
dojo.require("js.MultiComboBox");  // DOJO module adapted for SemanticScuttle
dojo.require("dijit.Tree");
</script>
<?php endif ?>