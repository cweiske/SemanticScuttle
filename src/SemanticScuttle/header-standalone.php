<?php
/**
 * Load this file instead of header.php if you
 * are using it in a standalone non-webserver script.
 *
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
$_SERVER['HTTP_HOST'] = 'http://localhost/';

require_once dirname(__FILE__) . '/header.php';
?>