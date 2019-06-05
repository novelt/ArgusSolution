<?php
/**
 * Created by PhpStorm.
 * User: nfargere
 * Date: 05-Dec-17
 * Time: 15:33
 */

namespace AppBundle\Tests\Utils;


use AppBundle\PhpReport\Header\ChartHeader;
use AppBundle\Tests\BaseKernelTestCase;

class RegularExpressionTest extends BaseKernelTestCase
{
    public function testReplaceUsingRegularExpression()
    {
        $stringEN = "Completeness (%)";
        $resultEN = ChartHeader::getCssClassNameFromColumnName($stringEN);
        $this->assertEquals("Completeness----", $resultEN);

        $stringFR = "Complétude (%)";
        $resultFR = ChartHeader::getCssClassNameFromColumnName($stringFR);
        $this->assertEquals("Complétude----", $resultFR);
    }
}