<?php namespace Core;

use Codeception\Test\Unit;
use Gzero\Core\Parsers\RangeParser;
use Gzero\InvalidArgumentException;
use Illuminate\Http\Request;

class RangeParserTest extends Unit {

    /** @var UnitTester */
    protected $tester;

    /** @test */
    public function isInstantiable()
    {
        $this->tester->assertInstanceOf(RangeParser::class, new RangeParser('age'));
    }

    /** @test */
    public function itCanParseBetweenMatch()
    {
        $parser = new RangeParser('age');
        $parser->parse(new Request(['age' => '1,5']));
        $this->assertEquals('between', $parser->getOperation());
        $this->assertEquals([1, 5], $parser->getValue());
    }

    /** @test */
    public function itCanParseNotBetweenMatch()
    {
        $parser = new RangeParser('age');
        $parser->parse(new Request(['age' => '!1,5']));
        $this->assertEquals('not between', $parser->getOperation());
        $this->assertEquals([1, 5], $parser->getValue());
    }

    /** @test */
    public function validationRuleShouldPass()
    {
        $parser = new RangeParser('name');
        $this->assertRegExp(explode('regex:', $parser->getValidationRule())[1], '1,2');
        $this->assertRegExp(explode('regex:', $parser->getValidationRule())[1], '!1,2');
        $this->assertRegExp(explode('regex:', $parser->getValidationRule())[1], '123,456');
        $this->assertRegExp(explode('regex:', $parser->getValidationRule())[1], '!123,456');
    }

    /** @test */
    public function validationRuleShouldNotPass()
    {
        $parser = new RangeParser('age');
        $this->assertNotRegExp(explode('regex:', $parser->getValidationRule())[1], 'jane,joe');
        $this->assertNotRegExp(explode('regex:', $parser->getValidationRule())[1], '!jane,joe');
        $this->assertNotRegExp(explode('regex:', $parser->getValidationRule())[1], '1, 2]');
        $this->assertNotRegExp(explode('regex:', $parser->getValidationRule())[1], '!house');
        $this->assertNotRegExp(explode('regex:', $parser->getValidationRule())[1], '!123');
        $this->assertNotRegExp(explode('regex:', $parser->getValidationRule())[1], '!2017-10-09,2017-10-10');
    }

    /** @test */
    public function shouldThrowExceptionForEmptyValue()
    {
        try {
            $parser = new RangeParser('age');
            $parser->parse(new Request(['age' => '']));
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('RangeParser: Value can\'t be empty', $exception->getMessage());
            return;
        }
        $this->fail('Exception should be thrown');
    }

    /** @test */
    public function shouldThrowExceptionForEmptyName()
    {
        try {
            new RangeParser('');
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('RangeParser: Name must be defined', $exception->getMessage());
            return;
        }
        $this->fail('Exception should be thrown');
    }
}
