<?php
/**
 * Ajax script to retrieve new Private Key
 *
 * PHP version 5.
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @author   Mark Pemberton <mpemberton5@gmail.com>
 * @license  AGPL http://www.gnu.org/licenses/agpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */

header("Last-Modified: ". gmdate("D, d M Y H:i:s") ." GMT");
header("Cache-Control: no-cache, must-revalidate");

$httpContentType = 'text/xml';
require_once 'www-header.php';

$us = SemanticScuttle_Service_Factory::get('User');

/* Managing all possible inputs */
isset($_GET['url']) ? define('GET_URL', $_GET['url']): define('GET_URL', '');

echo '<?xml version="1.0" encoding="utf-8"?>';
?>
<response>
<method>
getNewPrivateKey
</method>
<result>
<?php echo $us->getNewPrivateKey(); ?>
</result>
</response>
