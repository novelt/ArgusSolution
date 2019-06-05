<?php
/**
 * Created by PhpStorm.
 * User: nfargere
 * Date: 15/02/2017
 * Time: 15:33
 */

namespace AppBundle\Tests\Utils;


use AppBundle\Tests\BaseKernelTestCase;
use AppBundle\Utils\Parser;

class ParserTest extends BaseKernelTestCase
{
    /**
     * @var Parser
     */
    private $parser;

    public function setUp()
    {
        parent::setUp();
        $this->parser = $this->getService("parser");
    }

    public function testParseDate() {
        $this->assertNull($this->parser->parseDate(' '));
        $this->assertNull($this->parser->parseDate(null));
        $this->assertNull($this->parser->parseDate('xxx'));
        $this->assertEquals($this->parser->parseDate('2017-02-15'), new \DateTime('2017-02-15'));
        $this->assertEquals($this->parser->parseDate('2017/02/15'), new \DateTime('2017-02-15'));
    }

    public function testParseInteger() {
        $this->assertNull($this->parser->parseInteger(' '));
        $this->assertNull($this->parser->parseInteger(null));
        $this->assertNull($this->parser->parseInteger('xxx'));
        $this->assertEquals($this->parser->parseInteger('010'), 10);
        $this->assertEquals($this->parser->parseInteger('010.5'), 10);
    }

    public function testParseBoolean() {
        $this->assertNull($this->parser->parseBoolean(' '));
        $this->assertNull($this->parser->parseBoolean(null));
        $this->assertNull($this->parser->parseBoolean('xxx'));

        $this->assertTrue($this->parser->parseBoolean(1));
        $this->assertTrue($this->parser->parseBoolean('t'));
        $this->assertTrue($this->parser->parseBoolean('true'));
        $this->assertTrue($this->parser->parseBoolean(true));
        $this->assertTrue($this->parser->parseBoolean('True'));
        $this->assertTrue($this->parser->parseBoolean('TRUE'));
        $this->assertTrue($this->parser->parseBoolean('y'));
        $this->assertTrue($this->parser->parseBoolean('Y'));
        $this->assertTrue($this->parser->parseBoolean('Yes'));
        $this->assertTrue($this->parser->parseBoolean('YES'));

        $this->assertFalse($this->parser->parseBoolean(0));
        $this->assertFalse($this->parser->parseBoolean('f'));
        $this->assertFalse($this->parser->parseBoolean('false'));
        $this->assertFalse($this->parser->parseBoolean(false));
        $this->assertFalse($this->parser->parseBoolean('False'));
        $this->assertFalse($this->parser->parseBoolean('FALSE'));
        $this->assertFalse($this->parser->parseBoolean('n'));
        $this->assertFalse($this->parser->parseBoolean('N'));
        $this->assertFalse($this->parser->parseBoolean('No'));
        $this->assertFalse($this->parser->parseBoolean('NO'));
    }
}