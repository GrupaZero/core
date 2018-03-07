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
        $parser->parse(new Request(['name' => 'in[jane,joe]']));
        $this->assertEquals('in', $parser->getOperation());
        $this->assertEquals(['jane', 'joe'], $parser->getValue());
    }

    /** @test */
    public function itCanParseNotInMatch()
    {
        $parser = new ArrayParser('name');
        $parser->parse(new Request(['name' => 'notIn[jane,joe]']));
        $this->assertEquals('notIn', $parser->getOperation());
        $this->assertEquals(['jane', 'joe'], $parser->getValue());
    }

    /** @test */
    public function validationRuleShouldPass()
    {
        $parser = new ArrayParser('name');
        $this->assertRegExp(explode('regex:', $parser->getValidationRule())[1], 'in[jane,joe]');
        $this->assertRegExp(explode('regex:', $parser->getValidationRule())[1], 'notIn[house]');
    }

    /** @test */
    public function validationRuleShouldNotPass()
    {
        $parser = new ArrayParser('name');
        $this->assertNotRegExp(explode('regex:', $parser->getValidationRule())[1], 'injane,joe]');
        $this->assertNotRegExp(explode('regex:', $parser->getValidationRule())[1], 'notIn[house ]');
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

    /** @test */
    public function shouldThrowExceptionWithoutAnOpenBracket()
    {
        try {
            $parser = new ArrayParser('name');
            $parser->parse(new Request(['name' => 'notInjane,joe]']));
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('ArrayParser: Array has no open bracket ([)', $exception->getMessage());
            return;
        }
        $this->fail('Exception should be thrown');
    }

    /** @test */
    public function shouldThrowExceptionWithoutAClosingBracket()
    {
        try {
            $parser = new ArrayParser('name');
            $parser->parse(new Request(['name' => 'notIn[jane,joe']));
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('ArrayParser: Array has no closing bracket (])', $exception->getMessage());
            return;
        }
        $this->fail('Exception should be thrown');
    }

    /** @test */
    public function shouldThrowExceptionWhenLastLetterIsNotAClosingBracket()
    {
        try {
            $parser = new ArrayParser('name');
            $parser->parse(new Request(['name' => 'notIn[jane,joe]x']));
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('ArrayParser: Array has no closing bracket (])', $exception->getMessage());
            return;
        }
        $this->fail('Exception should be thrown');
    }
}
