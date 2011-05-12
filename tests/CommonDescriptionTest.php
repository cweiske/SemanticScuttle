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
 * Unit tests for the SemanticScuttle common description service.
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Benjamin Huynh-Kim-Bang <mensonge@users.sourceforge.net>
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @author   Eric Dane <ericdane@users.sourceforge.net>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */
class CommonDescriptionTest extends TestBase
{
    protected $us;
    protected $bs;
    protected $b2ts;
    protected $tts;
    protected $tsts;
    protected $cds;


    protected function setUp()
    {
        $this->us =SemanticScuttle_Service_Factory::get('User');
        $this->bs =SemanticScuttle_Service_Factory::get('Bookmark');
        $this->bs->deleteAll();
        $this->b2ts =SemanticScuttle_Service_Factory::get('Bookmark2Tag');
        $this->b2ts->deleteAll();
        $this->tts =SemanticScuttle_Service_Factory::get('Tag2Tag');
        $this->tts->deleteAll();
        $this->tsts =SemanticScuttle_Service_Factory::get('TagStat');
        $this->tsts->deleteAll();
        $this->cds =SemanticScuttle_Service_Factory::get('CommonDescription');
        $this->cds->deleteAll();
    }

    public function testModifyDescription()
    {
    $cds = $this->cds;

    $uId1 = 1;
    $uId2 = 2;
    $title1 = "title1";
    $title2 = "title2";
    $desc1 = "&é\"'(-è_çà)=´~#'#{{[\\\\[||`\^\^@^@}¹²¡×¿ ?./§µ%";
    $desc2 = "æâ€êþÿûîîôôöŀï'üð’‘ßä«≤»©»  ↓¿×÷¡¹²³";
    $time1 = time();
    $time2 = time()+200;

    $tagDesc1 = array('cdId'=>1, 'tag'=>'taghouse', 'cdDescription'=>$desc1, 'uId'=>$uId1,'cdDatetime'=>$time1);
    $tagDesc2 = array('cdId'=>2, 'tag'=>'taghouse', 'cdDescription'=>$desc2, 'uId'=>$uId2,'cdDatetime'=>$time2);

    $cds->addTagDescription('taghouse', $desc1, $uId1, $time1);
    $cds->addTagDescription('taghouse', $desc2, $uId2, $time2);

    $desc = $cds->getLastTagDescription('taghouse');
    $this->assertContains('taghouse', $desc);
    $this->assertContains($desc2, $desc);
    $this->assertContains(gmdate('Y-m-d H:i:s', $time2), $desc);

    $desc = $cds->getAllTagsDescription('taghouse');
    $this->assertContains($desc1, $desc[1]);
    $this->assertContains(gmdate('Y-m-d H:i:s', $time1), $desc[1]);
    $this->assertContains($desc2, $desc[0]);
    $this->assertContains(gmdate('Y-m-d H:i:s', $time2), $desc[0]);

    $desc = $cds->getDescriptionById(1);
    $this->assertContains($desc1, $desc);

    $bkDesc1 = array('cdId'=>3, 'bHash'=>'10', 'cdTitle'=>$title1, 'cdDescription'=>$desc1, 'uId'=>$uId1,'cdDatetime'=>$time1);
    $bkDesc2 = array('cdId'=>4, 'bHash'=>'10', 'cdTitle'=>$title2, 'cdDescription'=>$desc2, 'uId'=>$uId2,'cdDatetime'=>$time2);

    $cds->addBookmarkDescription(10, $title1, $desc1, $uId1, $time1);
    $cds->addBookmarkDescription(10, $title2, $desc2, $uId2, $time2);

    $desc = $cds->getLastBookmarkDescription(10);
    $this->assertContains($title2, $desc);
    $this->assertContains($desc2, $desc);
    $this->assertContains(gmdate('Y-m-d H:i:s', $time2), $desc);

    $desc = $cds->getAllBookmarksDescription(10);
    $this->assertContains($title1, $desc[1]);
    $this->assertContains($desc1, $desc[1]);
    $this->assertContains(gmdate('Y-m-d H:i:s', $time1), $desc[1]);
    $this->assertContains($title2, $desc[0]);
    $this->assertContains($desc2, $desc[0]);
    $this->assertContains(gmdate('Y-m-d H:i:s', $time2), $desc[0]);

    $desc = $cds->getDescriptionById(3);
    $this->assertContains($desc1, $desc);


    }

}
?>
