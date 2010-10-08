<?php
/**
 * Restores the semanticscuttle database from a given file.
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

if (!isset($argv[1])) {
    echo "Please pass the sql file to restore\n";
    exit(1);
}
$file = $argv[1];
if (!file_exists($file)) {
    echo "The file does not exist\n";
    exit(2);
}

require_once dirname(__FILE__) . '/../src/SemanticScuttle/header-standalone.php';

passthru(
    'mysql'
    . ' -h' . escapeshellarg($GLOBALS['dbhost'])
    . ' -u' . escapeshellarg($GLOBALS['dbuser'])
    . ' -p' . escapeshellarg($GLOBALS['dbpass'])
    . ' ' . escapeshellarg($GLOBALS['dbname'])
    . ' < ' . escapeshellarg($file)
);
?>