<?php

use Codeception\Test\Unit;
use Gzero\Core\Validators\Rules\Iso8601;

class Iso8601Test extends Unit {

    public function testIsoValidCases()
    {
        $this->assertTrue(Iso8601::test('2019-07-04T12:32:12-0300'));
        $this->assertTrue(Iso8601::test('2019-07-04T12:32:12+0300'));
        $this->assertTrue(Iso8601::test('2019-07-04T12:32:12-0000'));
        $this->assertTrue(Iso8601::test('2019-07-04T12:32:12+0000'));
        $this->assertTrue(Iso8601::test('2019-07-04T00:00:00+1130'));
    }

    public function testIsoInvalidCases()
    {
        $this->assertFalse(Iso8601::test('2019-07-04T12:32:12-03:00'));
        $this->assertFalse(Iso8601::test('2019-07-04T12:32:12+03:00'));
        $this->assertFalse(Iso8601::test('2019-07-04T12:32:120000'));
        $this->assertFalse(Iso8601::test('2019-07-04T12:32:12'));
        $this->assertFalse(Iso8601::test('2019-07-00T12:32:12+01:00'));
        $this->assertFalse(Iso8601::test('2019-00-04T12:32:12+01:00'));
        $this->assertFalse(Iso8601::test('2019-07-04 12:32:12+01:00'));
        $this->assertFalse(Iso8601::test('2019-07-04 12:32:12'));
        $this->assertFalse(Iso8601::test('2019-07-00T12:32+01:00'));
    }
}