<?php
/**
 * Dumps the semanticscuttle database into a file using mysqldump.
 *
 * This file is part of
 * SemanticScuttle - your social bookmark manager.
 *
 * PHP version 5.
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */
require_once dirname(__FILE__) . '/../src/SemanticScuttle/header-standalone.php';

passthru(
    'mysqldump'
    . ' -h' . escapeshellarg($GLOBALS['dbhost'])
    . ' -u' . escapeshellarg($GLOBALS['dbuser'])
    . ' -p' . escapeshellarg($GLOBALS['dbpass'])
    . ' ' . escapeshellarg($GLOBALS['dbname'])
    . ' > semanticscuttle-dump.sql'
);
?>