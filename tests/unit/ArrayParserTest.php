<?php namespace Core;

use Codeception\Test\Unit;
use Gzero\Core\Parsers\ArrayParser;
use Gzero\InvalidArgumentException;
use Illuminate\Http\Request;

class ArrayParserTest extends Unit {

    /** @var UnitTester */
    protected $tester;

    /** @test */
    public function isInstantiable()
    {
        $this->tester->assertInstanceOf(ArrayParser::class, new ArrayParser('name'));
    }

    /** @test */
    public function itCanParseInMatch()
    {
        $parser = new ArrayParser('name');
        $parser->parse(new Request(['name' => 'jane,joe']));
        $this->assertEquals('in', $parser->getOperation());
        $this->assertEquals(['jane', 'joe'], $parser->getValue());
    }

    /** @test */
    public function itCanParseNotInMatch()
    {
        $parser = new ArrayParser('name');
        $parser->parse(new Request(['name' => '!jane,joe']));
        $this->assertEquals('not in', $parser->getOperation());
        $this->assertEquals(['jane', 'joe'], $parser->getValue());
    }

    /** @test */
    public function validationRuleShouldPass()
    {
        $parser = new ArrayParser('name');
        $this->assertRegExp(explode('regex:', $parser->getValidationRule())[1], 'jane,joe');
        $this->assertRegExp(explode('regex:', $parser->getValidationRule())[1], '!house');
        $this->assertRegExp(explode('regex:', $parser->getValidationRule())[1], '!123');
    }

    /** @test */
    public function validationRuleShouldNotPass()
    {
        $parser = new ArrayParser('name');
        $this->assertNotRegExp(explode('regex:', $parser->getValidationRule())[1], 'jane, joe]');
        $this->assertNotRegExp(explode('regex:', $parser->getValidationRule())[1], '!2017-10-09,2017-10-10');
    }

    /** @test */
    public function itShouldConvertCtypeDigitStringsToIntegers()
    {
        $parser = new ArrayParser('name');
        $parser->parse(new Request(['name' => '1,2']));
        $this->assertEquals('in', $parser->getOperation());
        $this->assertEquals([1, 2], $parser->getValue());
    }

    /** @test */
    public function shouldWorkWithEmptyValue()
    {
        $parser = new ArrayParser('name');
        $parser->parse(new Request(['name' => '']));
        $this->assertEquals('in', $parser->getOperation());
        $this->assertNull($parser->getValue());
    }

    /** @test */
    public function shouldThrowExceptionForEmptyName()
    {
        try {
            new ArrayParser('');
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('ArrayParser: Name must be defined', $exception->getMessage());
            return;
        }
        $this->fail('Exception should be thrown');
    }
}
