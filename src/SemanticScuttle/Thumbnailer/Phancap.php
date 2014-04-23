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
 * Show website thumbnails/screenshots using phancap
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 * @see      http://cweiske.de/phancap.htm
 */
class SemanticScuttle_Thumbnailer_Phancap
{
    /**
     * Configuration array.
     * Required keys:
     * - url
     * - token
     * - secret
     */
    protected $config = array();

    /**
     * Set phancap configuration
     *
     * @param array $config Phancap configuration
     *
     * @return void
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * Get the URL for a website thumbnail
     *
     * @param string  $bookmarkUrl URL of website to create thumbnail for
     * @param integer $width       Screenshot width
     * @param integer $height      Screenshot height
     *
     * @return mixed FALSE when no screenshot could be obtained,
     *               string with the URL otherwise
     */
    public function getThumbnailUrl($bookmarkUrl, $width, $height)
    {
        //default parameters for the phancap service
        $parameters = array(
            'url'     => $bookmarkUrl,
            'swidth'  => $width,
            'sheight' => $height,
            'sformat' => 'jpg',
        );

        if (isset($this->config['token']) && $this->config['token'] != '') {
            $parameters['atoken']     = $this->config['token'];
            $parameters['atimestamp'] = time();

            //create signature
            ksort($parameters);
            foreach ($parameters as $key => $value) {
                $encparams[] = $key . '=' . rawurlencode($value);
            }
            $encstring = implode('&', $encparams);
            $signature = hash_hmac('sha1', $encstring, $this->config['secret']);
            //append signature to parameters
            $parameters['asignature'] = $signature;
        }

        //url-encode the parameters
        $urlParams = array();
        foreach ($parameters as $key => $value) {
            $urlParams[] = $key . '=' . urlencode($value);
        }

        //final URL
        return $this->config['url'] . '?' . implode('&', $urlParams);
    }
}
?>
