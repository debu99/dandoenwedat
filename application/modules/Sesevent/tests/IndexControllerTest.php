<?php 
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
include('/var/www/html/application/modules/Sesevent/controllers/IndexController.php');


final class IndexControllerTest extends TestCase {


    public function testGetAgeCategoriesToInterval(): void
    {
        $sampleA = array(18,29,40,51,62,73);
        $sampleB = array(18,29,40);
        $sampleC = array(18,29);
        $sampleD = array(18,62);
        $sampleE = array(18);

        $this->assertEqualsCanonicalizing(array("from"=> 18, "to"=> 88), Sesevent_IndexController::getAgeCategoriesToInterval($sampleA));
        $this->assertEqualsCanonicalizing(array("from"=> 18, "to"=> 50), Sesevent_IndexController::getAgeCategoriesToInterval($sampleB));
        $this->assertEqualsCanonicalizing(array("from"=> 18, "to"=> 39), Sesevent_IndexController::getAgeCategoriesToInterval($sampleC));
        $this->assertEqualsCanonicalizing(array("from"=> 18, "to"=>72), Sesevent_IndexController::getAgeCategoriesToInterval($sampleD));
        $this->assertEqualsCanonicalizing(array("from"=> 18, "to"=>28), Sesevent_IndexController::getAgeCategoriesToInterval($sampleE));
        $this->assertEqualsCanonicalizing(null, Sesevent_IndexController::getAgeCategoriesToInterval(null));
    }

    public function testGetIntervalToAgeCategories(): void 
    {
        $sampleA = array(
            "from" => 18,
            "to" => 88
        );
        $sampleAOut = array(
            '18'=> '18-28',
            '29'=> '29-39',
            '40'=> '40-50',
            '51'=> '51-61',
            '62'=> '62-72',
            '73'=> '73-88',
        );
        $sampleB = array(
            "from" => 18,
            "to" => 50
        );
        $sampleBOut = array(
            '18'=> '18-28',
            '29'=> '29-39',
            '40'=> '40-50'
        );
        $sampleC = array(
            "from" => 18,
            "to" => 39
        );
        $sampleCOut = array(
            '18'=> '18-28',
            '29'=> '29-39',
        );
        $sampleD = array(
            "from" => 18,
            "to" => 72
        );
        $sampleDOut = array(
            '18'=> '18-28',
            '29'=> '29-39',
            '40'=> '40-50',
            '51'=> '51-61',
            '62'=> '62-72',
        );
        $sampleE = array(
            "from" => 18,
            "to" => 28
        );
        $sampleEOut = array(
            '18'=> '18-28'
        );
        $this->assertEqualsCanonicalizing($sampleAOut, Sesevent_IndexController::getIntervalToAgeCategories($sampleA));
        $this->assertEqualsCanonicalizing($sampleBOut, Sesevent_IndexController::getIntervalToAgeCategories($sampleB));
        $this->assertEqualsCanonicalizing($sampleCOut, Sesevent_IndexController::getIntervalToAgeCategories($sampleC));
        $this->assertEqualsCanonicalizing($sampleDOut, Sesevent_IndexController::getIntervalToAgeCategories($sampleD));
        $this->assertEqualsCanonicalizing($sampleEOut, Sesevent_IndexController::getIntervalToAgeCategories($sampleE));
        $this->assertEqualsCanonicalizing(null, Sesevent_IndexController::getIntervalToAgeCategories(null));
    }
}

