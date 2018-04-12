<?php namespace Core;

use Codeception\Test\Unit;
use Gzero\Core\Parsers\DateParser;
use Gzero\InvalidArgumentException;
use Illuminate\Http\Request;

class DateParserTest extends Unit {

    /** @var UnitTester */
    protected $tester;

    /** @test */
    public function isInstantiable()
    {
        $this->tester->assertInstanceOf(DateParser::class, new DateParser('field'));
    }

    /** @test */
    public function itCanParseExactMatch()
    {
        $parser = new DateParser('date');
        $parser->parse(new Request(['date' => '2018-04-12']));
        $this->assertEquals('=', $parser->getOperation());
        $this->assertEquals('2018-04-12', $parser->getValue());
    }

    /** @test */
    public function itCanParseNegatedExactMatch()
    {
        $parser = new DateParser('date');
        $parser->parse(new Request(['date' => '!2018-04-12']));
        $this->assertEquals('!=', $parser->getOperation());
        $this->assertEquals('2018-04-12', $parser->getValue());
    }

    /** @test */
    public function itCanParseGreaterThanAndEqualTo()
    {
        $parser = new DateParser('date');
        $parser->parse(new Request(['date' => '>=2018-04-12']));
        $this->assertEquals('>=', $parser->getOperation());
        $this->assertEquals('2018-04-12', $parser->getValue());
    }

    /** @test */
    public function itCanParseLessThanAndEqualTo()
    {
        $parser = new DateParser('date');
        $parser->parse(new Request(['date' => '<=2018-04-12']));
        $this->assertEquals('<=', $parser->getOperation());
        $this->assertEquals('2018-04-12', $parser->getValue());
    }

    /** @test */
    public function itCanParseGreaterThan()
    {
        $parser = new DateParser('date');
        $parser->parse(new Request(['date' => '>2018-04-12']));
        $this->assertEquals('>', $parser->getOperation());
        $this->assertEquals('2018-04-12', $parser->getValue());
    }

    /** @test */
    public function itCanParseLessThan()
    {
        $parser = new DateParser('date');
        $parser->parse(new Request(['date' => '<2018-04-12']));
        $this->assertEquals('<', $parser->getOperation());
        $this->assertEquals('2018-04-12', $parser->getValue());
    }

    /** @test */
    public function validationRuleShouldPass()
    {
        $parser = new DateParser('date');
        $this->assertRegExp(explode('regex:', $parser->getValidationRule())[1], '2017-10-09');
        $this->assertRegExp(explode('regex:', $parser->getValidationRule())[1], '!2017-10-09');
        $this->assertRegExp(explode('regex:', $parser->getValidationRule())[1], '>=2017-10-09');
        $this->assertRegExp(explode('regex:', $parser->getValidationRule())[1], '<=2017-10-09');
        $this->assertRegExp(explode('regex:', $parser->getValidationRule())[1], '>2017-10-09');
        $this->assertRegExp(explode('regex:', $parser->getValidationRule())[1], '<2017-10-09');
    }

    /** @test */
    public function validationRuleShouldNotPass()
    {
        $parser = new DateParser('date');
        $this->assertNotRegExp(explode('regex:', $parser->getValidationRule())[1], 'text');
        $this->assertNotRegExp(explode('regex:', $parser->getValidationRule())[1], '!text');
        $this->assertNotRegExp(explode('regex:', $parser->getValidationRule())[1], '>=text');
        $this->assertNotRegExp(explode('regex:', $parser->getValidationRule())[1], '<=text');
        $this->assertNotRegExp(explode('regex:', $parser->getValidationRule())[1], '>text');
        $this->assertNotRegExp(explode('regex:', $parser->getValidationRule())[1], '<text');
        $this->assertNotRegExp(explode('regex:', $parser->getValidationRule())[1], '2017-10-09,2017-10-10');
        $this->assertNotRegExp(explode('regex:', $parser->getValidationRule())[1], '!2017-10-09,2017-10-10');
    }

    /** @test */
    public function shouldThrowExceptionForInvalidDate()
    {
        try {
            $parser = new DateParser('date');
            $parser->parse(new Request(['date' => '<10']));
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('DateParser: Value must be a valid date', $exception->getMessage());
            return;
        }
        $this->fail('Exception should be thrown');
    }

    /** @test */
    public function shouldThrowExceptionForEmptyName()
    {
        try {
            new DateParser('', '');
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('DateParser: Name must be defined', $exception->getMessage());
            return;
        }
        $this->fail('Exception should be thrown');
    }

    /** @test */
    public function shouldThrowExceptionForEmptyValue()
    {
        try {
            $parser = new DateParser('date');
            $parser->parse(new Request(['date' => '']));
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('DateParser: Value must be a valid date', $exception->getMessage());
            return;
        }
        $this->fail('Exception should be thrown');
    }

}
