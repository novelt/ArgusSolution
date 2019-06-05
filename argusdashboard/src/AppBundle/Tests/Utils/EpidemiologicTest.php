<?php
/**
 * Created by PhpStorm.
 * User: nfargere
 * Date: 15/11/2016
 * Time: 16:16
 */

namespace AppBundle\Tests\Utils;


use AppBundle\Tests\BaseKernelTestCase;
use AppBundle\Utils\Epidemiologic;

class EpidemiologicTest extends BaseKernelTestCase
{
    public function testGetNumberOfWeeksInYear() {
        $nb = Epidemiologic::getNumberOfWeeksInYear(2016, 1);
        $this->assertEquals(52, $nb);
        $nb = Epidemiologic::getNumberOfWeeksInYear(2016, 4);
        $this->assertEquals(52, $nb);
        $nb = Epidemiologic::getNumberOfWeeksInYear(2016, 3);
        $this->assertEquals(53, $nb);
    }
}