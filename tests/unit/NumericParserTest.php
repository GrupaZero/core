<?php namespace Core;

use Codeception\Test\Unit;
use Gzero\Core\Parsers\NumericParser;
use Gzero\InvalidArgumentException;
use Illuminate\Http\Request;

class NumericParserTest extends Unit {

    /** @var UnitTester */
    protected $tester;

    /** @test */
    public function isInstantiable()
    {
        $this->tester->assertInstanceOf(NumericParser::class, new NumericParser('field'));
    }

    /** @test */
    public function itCanParseExactMatch()
    {
        $parser = new NumericParser('number');
        $parser->parse(new Request(['number' => 123]));
        $this->assertEquals('=', $parser->getOperation());
        $this->assertEquals(123, $parser->getValue());
    }

    /** @test */
    public function itCanParseNegatedExactMatch()
    {
        $parser = new NumericParser('number');
        $parser->parse(new Request(['number' => '!123']));
        $this->assertEquals('!=', $parser->getOperation());
        $this->assertEquals(123, $parser->getValue());
    }

    /** @test */
    public function itCanParseGreaterThanAndEqualTo()
    {
        $parser = new NumericParser('number');
        $parser->parse(new Request(['number' => '>=123']));
        $this->assertEquals('>=', $parser->getOperation());
        $this->assertEquals(123, $parser->getValue());
    }

    /** @test */
    public function itCanParseLessThanAndEqualTo()
    {
        $parser = new NumericParser('number');
        $parser->parse(new Request(['number' => '<=123']));
        $this->assertEquals('<=', $parser->getOperation());
        $this->assertEquals(123, $parser->getValue());
    }

    /** @test */
    public function itCanParseGreaterThan()
    {
        $parser = new NumericParser('number');
        $parser->parse(new Request(['number' => '>123']));
        $this->assertEquals('>', $parser->getOperation());
        $this->assertEquals(123, $parser->getValue());
    }

    /** @test */
    public function itCanParseLessThan()
    {
        $parser = new NumericParser('number');
        $parser->parse(new Request(['number' => '<123']));
        $this->assertEquals('<', $parser->getOperation());
        $this->assertEquals(123, $parser->getValue());
    }

    /** @test */
    public function itCanNotParseStringValue()
    {
        try {
            $parser = new NumericParser('size');
            $parser->parse(new Request(['size' => '<ten']));
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('NumericParser: Value must be of type numeric', $exception->getMessage());
            return;
        }
        $this->fail('Exception should be thrown');
    }

    /** @test */
    public function shouldWorkWithEmptyValue()
    {
        $parser = new NumericParser('xyz');
        $parser->parse(new Request(['xyz' => '']));
        $this->assertEquals('=', $parser->getOperation());
        $this->assertNull($parser->getValue());
    }

    /** @test */
    public function shouldThrowExceptionForEmptyName()
    {
        try {
            new NumericParser('', '');
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('NumericParser: Name must be defined', $exception->getMessage());
            return;
        }
        $this->fail('Exception should be thrown');
    }

}
