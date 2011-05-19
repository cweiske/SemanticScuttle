<?php
/**
 * SemanticScuttle - your social bookmark manager.
 *
 * PHP version 5.
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Benjamin Huynh-Kim-Bang <mensonge@users.sourceforge.net>
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @author   Eric Dane <ericdane@users.sourceforge.net>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */

/**
 * Prepare the application for unit testing
 */
//that's needed in constants.php
$_SERVER['HTTP_HOST'] = 'http://localhost/';

define('UNIT_TEST_MODE', true);

if ('@data_dir@' == '@' . 'data_dir@') {
    //non pear-installation (i.e. git checkout)
    require_once dirname(__FILE__) . '/../src/SemanticScuttle/header.php';
} else {
    //pear installation; files are in include path
    require_once 'SemanticScuttle/header.php';
}
require_once dirname(__FILE__) . '/TestBase.php';
require_once dirname(__FILE__) . '/TestBaseApi.php';

if ($GLOBALS['debugMode'] == true
    && $GLOBALS['dbtype'] == 'mysql4'
) {
    echo "\n"
        . '!! The combination of debugMode and dbtype==mysql4'
        . ' will wreck some tests' . "\n\n";
}
?>