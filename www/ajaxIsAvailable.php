<?php
/***************************************************************************
Copyright (C) 2006 Scuttle project
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

header("Last-Modified: ". gmdate("D, d M Y H:i:s") ." GMT");
header("Cache-Control: no-cache, must-revalidate");

$httpContentType = 'text/xml';
require_once 'www-header.php';

/* Service creation: only useful services are created */
//No specific services

/* Managing all possible inputs */
isset($_GET['username']) ? define('GET_USERNAME', $_GET['username']): define('GET_USERNAME', '');


if ($userservice->isReserved(GET_USERNAME)) {
    $result = 'false';
} else {
    $result = $userservice->getUserByUsername(GET_USERNAME) ? 'false' : 'true';
}
?>
<response>
  <method>isAvailable</method>
  <result><?php echo $result; ?></result>
</response>