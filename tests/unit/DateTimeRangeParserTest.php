<?php namespace Core;

use Carbon\Carbon;
use Codeception\Test\Unit;
use Gzero\Core\Parsers\DateTimeRangeParser;
use Gzero\InvalidArgumentException;
use Illuminate\Http\Request;

class DateRangeParserTest extends Unit {

    /** @var UnitTester */
    protected $tester;

    /** @test */
    public function isInstantiable()
    {
        $this->tester->assertInstanceOf(DateTimeRangeParser::class, new DateTimeRangeParser('date'));
    }

    /** @test */
    public function itCanParseBetweenMatch()
    {
        $from = Carbon::parse('2021-05-02 0:43:31', 'Australia/Adelaide');
        $to   = Carbon::parse('2021-05-01 23:43:31', 'America/New_York');

        $parser = new DateTimeRangeParser('date');
        $parser->parse(new Request(['date' => $from->toIso8601String() . "," . $to->toIso8601String()]));
        $this->assertEquals('between', $parser->getOperation());
        $result = $parser->getValue();

        $this->assertEquals($from, $result[0]);
        $this->assertEquals('UTC', $result[0]->getTimezone()->getName());

        $this->assertEquals($to, $result[1]);
        $this->assertEquals('UTC', $result[1]->getTimezone()->getName());
    }

    /** @test */
    public function itCanParseNotBetweenMatch()
    {
        $from = Carbon::parse('2021-05-02 0:43:31', 'Australia/Adelaide');
        $to   = Carbon::parse('2021-05-01 23:43:31', 'America/New_York');

        $parser = new DateTimeRangeParser('date');
        $parser->parse(new Request(['date' => "!" . $from->toIso8601String() . "," . $to->toIso8601String()]));
        $this->assertEquals('not between', $parser->getOperation());
        $result = $parser->getValue();

        $this->assertEquals($from, $result[0]);
        $this->assertEquals('UTC', $result[0]->getTimezone()->getName());

        $this->assertEquals($to, $result[1]);
        $this->assertEquals('UTC', $result[1]->getTimezone()->getName());
    }

    /** @test */
    public function validationRuleShouldPass()
    {
        $parser = new DateTimeRangeParser('name');

        $this->assertRegExp(explode('regex:', $parser->getValidationRule())[1], '2017-10-09T02:03:01-05:30,2017-10-07T02:03:01+05:30');
        $this->assertRegExp(explode('regex:', $parser->getValidationRule())[1], '!2017-10-09T02:03:01-05:30,2017-10-07T02:03:01+05:30');
    }

    /** @test */
    public function validationRuleShouldNotPass()
    {
        $parser = new DateTimeRangeParser('date');
        $this->assertNotRegExp(explode('regex:', $parser->getValidationRule())[1], '2017-10-09,2017-10-10');
        $this->assertNotRegExp(explode('regex:', $parser->getValidationRule())[1], '!2017-10-09,2017-10-10');
        $this->assertNotRegExp(explode('regex:', $parser->getValidationRule())[1], '2017-10-09T03:23,2017-10-10 02:22');
        $this->assertNotRegExp(explode('regex:', $parser->getValidationRule())[1], '!2017-10-09T03:23,2017-10-10 02:22');
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
            $parser = new DateTimeRangeParser('date');
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
            new DateTimeRangeParser('');
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('DateRangeParser: Name must be defined', $exception->getMessage());
            return;
        }
        $this->fail('Exception should be thrown');
    }
}
