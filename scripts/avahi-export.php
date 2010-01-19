<?php
/**
 * Exports bookmarks tagged with "zeroconf"
 * as avahi service files.
 *
 * XML Documentation: "man 5 avahi.service"
 *
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

$fileprefix = $GLOBALS['avahiServiceFilePrefix'];
$filepath   = $GLOBALS['avahiServiceFilePath'];

$arSchemes = array(
    'ftp'  => array(21, '_ftp._tcp'),
    'ssh'  => array(22, '_ftp._tcp'),
    'sftp' => array(22, '_sftp-ssh._tcp'),
    'http' => array(80, '_http._tcp'),
);

if (!is_writable($filepath)) {
    echo "avahi service directory is not writable:\n";
    echo $filepath . "\n";
    exit(1);
}

//clean out existing SemanticScuttle service files
$existing = glob($filepath . '/' . $fileprefix . '*');
if (count($existing) > 0) {
    foreach ($existing as $file) {
        unlink($file);
    }
}

$bs = SemanticScuttle_Service_Factory::get('Bookmark');
$bookmarks = $bs->getBookmarks(0, null, null, $GLOBALS['avahiTagName']);
$bookmarks = $bookmarks['bookmarks'];

if (count($bookmarks) == 0) {
    echo 'No "' . $GLOBALS['avahiTagName'] . '"-tagged bookmarks available.' . "\n";
    exit(0);
}

$written = 0;
foreach ($bookmarks as $bm) {
    $xTitle = htmlspecialchars($bm['bTitle']);
    $parts  = parse_url($bm['bAddress']);

    if (!isset($parts['host'])) {
        echo 'No hostname in: ' . $bm['bAddress'] . "\n";
        exit(2);
    }

    $xHostname = htmlspecialchars($parts['host']);
    $xPath     = isset($parts['path']) ? $parts['path'] : '';
    if (isset($parts['query'])) {
        $xPath .= '?' . $parts['query'];
    }
    if (isset($parts['fragment'])) {
        $xPath .= '#' . $parts['fragment'];
    }

    $scheme = isset($parts['scheme']) ? $parts['scheme'] : 'http';
    if (!isset($arSchemes[$scheme])) {
        //dying is hard, but at least the user knows
        // that something is seriously wrong
        echo "Unknown scheme: $scheme\n";
        exit(3);
    }
    list($xPort, $xType) = $arSchemes[$scheme];

    if (isset($parts['port'])) {
        $xPort = (int)$parts['port'];
    }

    $xml = <<<XML
<?xml version="1.0" standalone='no'?>
<!DOCTYPE service-group SYSTEM "avahi-service.dtd">
<service-group>
  <name>{$xTitle}</name>
  <service>
    <type>{$xType}</type>
    <host-name>{$xHostname}</host-name>
    <port>{$xPort}</port>
    <txt-record>path={$xPath}</txt-record>
  </service>
</service-group>
XML;

    $file = $filepath . '/' . $fileprefix . $bm['bId'] . '.service';
    file_put_contents($file, $xml);
    ++$written;
}

echo $written . " service files created\n";
?>