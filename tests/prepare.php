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
$_SERVER['HTTP_HOST'] = 'http://localhost/';
define('UNIT_TEST_MODE', true);

require_once dirname(__FILE__) . '/../src/SemanticScuttle/header.php';
require_once dirname(__FILE__) . '/TestBase.php';
?>