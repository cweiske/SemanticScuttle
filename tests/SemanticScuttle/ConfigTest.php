<?php
//that's PEAR's Stream_Var package
require_once 'Stream/Var.php';

class SemanticScuttle_ConfigTest_StreamVar extends Stream_Var {
    public function url_stat($path, $flags)
    {
        $url = parse_url($path);

        $scope   = $url['host'];
        if (isset($url['path'])) {
            $varpath = substr($url['path'], 1);
        } else {
            $varpath = '';
        }

        if (!$this->_setPointer($scope, $varpath)) {
            return false;
        }

        return parent::url_stat($path, $flags);
    }
}

class SemanticScuttle_ConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * Configuration object to test
     */
    protected $cfg;


    public function setUpWrapper()
    {
        if (!in_array('unittest', stream_get_wrappers())) {
            stream_wrapper_register(
                'unittest', 'SemanticScuttle_ConfigTest_StreamVar'
            );
        }

        $this->cfg = $this->getMock(
            'SemanticScuttle_Config',
            array('getDataDir')
        );
        $this->cfg->expects($this->once())
            ->method('getDataDir')
            ->will($this->returnValue('/data-dir/'));

        $this->cfg->filePrefix = 'unittest://GLOBALS/unittest-dir';
    }



    public function testFindLocalData()
    {
        $this->setUpWrapper();
        $GLOBALS['unittest-dir']['data-dir'] = array(
            'config.php' => 'content',
            'config.default.php' => 'content'
        );
        $this->assertEquals(
            array(
                '/data-dir/config.php',
                '/data-dir/config.default.php'
            ),
            $this->cfg->findFiles()
        );
    }

    public function testFindHostPreferredOverNonHostConfig()
    {
        $this->setUpWrapper();
        $_SERVER['HTTP_HOST'] = 'foo.example.org';

        $GLOBALS['unittest-dir']['data-dir'] = array(
            'config.php' => 'content',
            'config.foo.example.org.php' => 'content',
            'config.default.php' => 'content'
        );
        $this->assertEquals(
            array(
                '/data-dir/config.foo.example.org.php',
                '/data-dir/config.default.php'
            ),
            $this->cfg->findFiles()
        );
    }

    public function testFindEtcHostPreferredOverLocalConfigPhp()
    {
        $this->setUpWrapper();
        $_SERVER['HTTP_HOST'] = 'foo.example.org';

        $GLOBALS['unittest-dir'] = array(
            'etc' => array(
                'semanticscuttle' => array(
                    'config.foo.example.org.php' => 'content',
                )
            ),
            'data-dir' => array(
                'config.php' => 'content',
                'config.default.php' => 'content'
            )
        );

        $this->assertEquals(
            array(
                '/etc/semanticscuttle/config.foo.example.org.php',
                '/data-dir/config.default.php'
            ),
            $this->cfg->findFiles()
        );
    }

    public function testFindEtcConfig()
    {
        $this->setUpWrapper();
        $GLOBALS['unittest-dir'] = array(
            'etc' => array(
                'semanticscuttle' => array(
                    'config.php' => 'content'
                )
            ),
            'data-dir' => array(
                'config.default.php' => 'content'
            )
        );
        $this->assertEquals(
            array(
                '/etc/semanticscuttle/config.php',
                '/data-dir/config.default.php'
            ),
            $this->cfg->findFiles()
        );
    }

    public function testFindEtcDefaultConfig()
    {
        $this->setUpWrapper();
        $GLOBALS['unittest-dir'] = array(
            'etc' => array(
                'semanticscuttle' => array(
                    'config.php' => 'content',
                    'config.default.php' => 'content'
                )
            ),
        );
        $this->assertEquals(
            array(
                '/etc/semanticscuttle/config.php',
                '/etc/semanticscuttle/config.default.php'
            ),
            $this->cfg->findFiles()
        );
    }

    public function testFindLocalDefaultPreferredOverEtcDefault()
    {
        $this->setUpWrapper();
        $GLOBALS['unittest-dir'] = array(
            'etc' => array(
                'semanticscuttle' => array(
                    'config.php' => 'content',
                    'config.default.php' => 'content'
                )
            ),
            'data-dir' => array(
                'config.php' => 'content',
                'config.default.php' => 'content'
            )
        );
        $this->assertEquals(
            array(
                '/data-dir/config.php',
                '/data-dir/config.default.php'
            ),
            $this->cfg->findFiles()
        );
    }

    public function testFindSameDirDefaultPreferred()
    {
        $this->setUpWrapper();
        $GLOBALS['unittest-dir'] = array(
            'etc' => array(
                'semanticscuttle' => array(
                    'config.php' => 'content',
                    'config.default.php' => 'content'
                )
            ),
            'data-dir' => array(
                'config.default.php' => 'content'
            )
        );
        $this->assertEquals(
            array(
                '/etc/semanticscuttle/config.php',
                '/etc/semanticscuttle/config.default.php'
            ),
            $this->cfg->findFiles()
        );
    }

}

?>