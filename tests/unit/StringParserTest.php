<?php namespace Core;

use Codeception\Test\Unit;
use Gzero\InvalidArgumentException;
use Gzero\Core\Parsers\StringParser;
use Illuminate\Http\Request;

class StringParserTest extends Unit {

    /** @var UnitTester */
    protected $tester;

    /** @test */
    public function isInstantiable()
    {
        $this->tester->assertInstanceOf(StringParser::class, new StringParser('field'));
    }

    /** @test */
    public function itCanParseExactMatch()
    {
        $parser = new StringParser('some_number');
        $parser->parse(new Request(['some_number' => '123-123-123']));
        $this->assertEquals('=', $parser->getOperation());
        $this->assertEquals('123-123-123', $parser->getValue());
    }

    /** @test */
    public function itCanParseNegatedExactMatch()
    {
        $parser = new StringParser('some_number');
        $parser->parse(new Request(['some_number' => '!123-123-123']));
        $this->assertEquals('!=', $parser->getOperation());
        $this->assertEquals('123-123-123', $parser->getValue());
    }

    /** @test */
    public function shouldWorkWithEmptyValue()
    {
        $parser = new StringParser('xyz');
        $parser->parse(new Request(['xyz' => '']));
        $this->assertEquals('=', $parser->getOperation());
        $this->assertNull($parser->getValue());
    }

    /** @test */
    public function shouldThrowExceptionForEmptyName()
    {
        try {
            new StringParser('', '');
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('StringParser: Name must be defined', $exception->getMessage());
            return;
        }
        $this->fail('Exception should be thrown');
    }

}
