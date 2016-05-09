<?php
/**
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

/**
 * Work with URLs
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */
class SemanticScuttle_UrlHelper
{
    function getTitle($url)
    {
        $fd = @fopen($url, 'r');
        $title = '';
        if ($fd) {
            $html = fread($fd, 1750);
            fclose($fd);

            // Get title from title tag
            preg_match_all('/<title[^>]*>(.*)<\/title>/si', $html, $matches);
            $title = $matches[1][0];

            $encoding = 'utf-8';
            // Get encoding from charset attribute
            preg_match_all('/<meta.*charset=([^;"]*)">/i', $html, $matches);
            if (isset($matches[1][0])) {
                $encoding = strtoupper($matches[1][0]);
            }

            // Convert to UTF-8 from the original encoding
            if (function_exists("mb_convert_encoding")) {
                $title = @mb_convert_encoding($title, 'UTF-8', $encoding);
            }

            $title = trim($title);
        }

        if (utf8_strlen($title) > 0) {
            $title = html_entity_decode($title, ENT_QUOTES, 'UTF-8');
            return $title;
        } else {
            // No title, so return filename
            $uriparts = explode('/', $url);
            $filename = end($uriparts);
            unset($uriparts);

            return $filename;
        }
    }
}
?>
