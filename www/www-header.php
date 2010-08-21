<?php
/**
 * Base file that every file in www/ should include.
 * Loads all other SemanticScuttle files.
 *
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
if ('@data_dir@' == '@' . 'data_dir@') {
    //non pear-install
    require_once dirname(__FILE__) . '/../src/SemanticScuttle/header.php';
} else {
    //pear installation; files are in include path
    require_once 'SemanticScuttle/header.php';
}
?>