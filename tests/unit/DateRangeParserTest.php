<?php namespace Core;

use Codeception\Test\Unit;
use Gzero\Core\Parsers\DateRangeParser;
use Gzero\InvalidArgumentException;
use Illuminate\Http\Request;

class DateRangeParserTest extends Unit {

    /** @var UnitTester */
    protected $tester;

    /** @test */
    public function isInstantiable()
    {
        $this->tester->assertInstanceOf(DateRangeParser::class, new DateRangeParser('date'));
    }

    /** @test */
    public function itCanParseBetweenMatch()
    {
        $parser = new DateRangeParser('date');
        $parser->parse(new Request(['date' => '2017-10-09,2017-10-10']));
        $this->assertEquals('between', $parser->getOperation());
        $this->assertEquals(['2017-10-09', '2017-10-10'], $parser->getValue());
    }

    /** @test */
    public function itCanParseNotBetweenMatch()
    {
        $parser = new DateRangeParser('date');
        $parser->parse(new Request(['date' => '!2017-10-09,2017-10-10']));
        $this->assertEquals('not between', $parser->getOperation());
        $this->assertEquals(['2017-10-09', '2017-10-10'], $parser->getValue());
    }

    /** @test */
    public function validationRuleShouldPass()
    {
        $parser = new DateRangeParser('name');
        $this->assertRegExp(explode('regex:', $parser->getValidationRule())[1], '2017-10-09,2017-10-10');
        $this->assertRegExp(explode('regex:', $parser->getValidationRule())[1], '!2017-10-09,2017-10-10');
    }

    /** @test */
    public function validationRuleShouldNotPass()
    {
        $parser = new DateRangeParser('date');
        $this->assertNotRegExp(explode('regex:', $parser->getValidationRule())[1], 'jane,joe');
        $this->assertNotRegExp(explode('regex:', $parser->getValidationRule())[1], '!jane,joe');
        $this->assertNotRegExp(explode('regex:', $parser->getValidationRule())[1], '1, 2]');
        $this->assertNotRegExp(explode('regex:', $parser->getValidationRule())[1], '!house');
        $this->assertNotRegExp(explode('regex:', $parser->getValidationRule())[1], '!123');
    }

    /** @test */
    public function shouldThrowExceptionForEmptyValue()
    {
        try {
            $parser = new DateRangeParser('date');
            $parser->parse(new Request(['date' => '']));
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('DateRangeParser: Value can\'t be empty', $exception->getMessage());
            return;
        }
        $this->fail('Exception should be thrown');
    }

    /** @test */
    public function shouldThrowExceptionForEmptyName()
    {
        try {
            new DateRangeParser('');
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('DateRangeParser: Name must be defined', $exception->getMessage());
            return;
        }
        $this->fail('Exception should be thrown');
    }
}
