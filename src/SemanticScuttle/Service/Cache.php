<?php
class SemanticScuttle_Service_Cache extends SemanticScuttle_Service
{
    var $basedir;
    var $fileextension = '.cache';

    /**
     * Returns the single service instance
     *
     * @param DB $db Database object
     *
     * @return SemanticScuttle_Service
     */
	public static function getInstance($db)
    {
		static $instance;
		if (!isset($instance)) {
            $instance = new self($db);
        }
		return $instance;
	}

    protected function __construct()
    {
        $this->basedir = $GLOBALS['dir_cache'];    
    }

    function Start($hash, $time = 300) {
        $cachefile = $this->basedir .'/'. $hash . $this->fileextension;
        if (file_exists($cachefile) && time() < filemtime($cachefile) + $time) {
            @readfile($cachefile);
            echo "\n<!-- Cached: ". date('r', filemtime($cachefile)) ." -->\n";
            unset($cachefile);
            exit;
        }
        ob_start("ob_gzhandler");
    }

    function End($hash) {
        $cachefile = $this->basedir .'/'. $hash . $this->fileextension;      
        $handle = fopen($cachefile, 'w');
        fwrite($handle, ob_get_contents());
        fclose($handle);
        ob_flush();
    }
}
?>