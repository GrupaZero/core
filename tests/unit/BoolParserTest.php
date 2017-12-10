<?php namespace Core;

use Codeception\Test\Unit;
use Gzero\Core\Exception;
use Gzero\Core\Parsers\BoolParser;
use Illuminate\Http\Request;

class BoolParserTest extends Unit {

    /** @var UnitTester */
    protected $tester;

    /** @test */
    public function isInstantiable()
    {
        $this->tester->assertInstanceOf(BoolParser::class, new BoolParser('is_active'));
    }

    /** @test */
    public function itCanParseBooleanMatchWhenValueIsOfTypeBoolean()
    {
        $parser = new BoolParser('is_active');
        $parser->parse(new Request(['is_active' => true]));
        $this->assertEquals('=', $parser->getOperation());
        $this->assertEquals(true, $parser->getValue());
    }

    /** @test */
    public function itCanParseBooleanMatchWhenValueIsOfTypeInteger()
    {
        $parser = new BoolParser('is_active');
        $parser->parse(new Request(['is_active' => 1]));
        $this->assertEquals('=', $parser->getOperation());
        $this->assertEquals(true, $parser->getValue());
    }

    /** @test */
    public function itCanParseBooleanMatchWhenValueIsOfTypeString()
    {
        $parser = new BoolParser('is_active');
        $parser->parse(new Request(['is_active' => '1']));
        $this->assertEquals('=', $parser->getOperation());
        $this->assertEquals(true, $parser->getValue());
    }

    /** @test */
    public function itCanParseNegatedBooleanMatchWhenValueIsOfTypeBoolean()
    {
        $parser = new BoolParser('is_active');
        $parser->parse(new Request(['is_active' => false]));
        $this->assertEquals('=', $parser->getOperation());
        $this->assertEquals(false, $parser->getValue());
    }

    /** @test */
    public function itCanParseNegatedBooleanMatchWhenValueIsOfTypeInteger()
    {
        $parser = new BoolParser('is_active');
        $parser->parse(new Request(['is_active' => 0]));
        $this->assertEquals('=', $parser->getOperation());
        $this->assertEquals(false, $parser->getValue());
    }

    /** @test */
    public function itCanParseNegatedBooleanMatchWhenValueIsOfTypeString()
    {
        $parser = new BoolParser('is_active');
        $parser->parse(new Request(['is_active' => '0']));
        $this->assertEquals('=', $parser->getOperation());
        $this->assertEquals(false, $parser->getValue());
    }

    /** @test */
    public function shouldWorkWithEmptyValue()
    {
        $parser = new BoolParser('xyz');
        $parser->parse(new Request(['xyz' => '']));
        $this->assertEquals('=', $parser->getOperation());
        $this->assertNull($parser->getValue());
    }

    /** @test */
    public function shouldThrowExceptionForEmptyName()
    {
        try {
            new BoolParser('', '');
        } catch (Exception $exception) {
            $this->assertEquals('BoolParser: Name must be defined', $exception->getMessage());
            return;
        }
        $this->fail('Exception should be thrown');
    }

}
