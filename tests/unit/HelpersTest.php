<?php

use Carbon\Carbon;
use Codeception\Test\Unit;

class HelpersTest extends Unit {

    /** @test */
    public function itCanConvertDateTimeObjectToOtherTimezone()
    {
        $dateTime = Carbon::parse('2021-05-02 12:43:31', 'America/New_York');
        $dateTimeInOtherTimezone = dateTimeToOtherTimezone($dateTime, 'Australia/Adelaide');

        $this->assertEquals($dateTime, $dateTimeInOtherTimezone);
        $this->assertEquals('Australia/Adelaide', $dateTimeInOtherTimezone->getTimezone()->getName());
    }

    /** @test */
    public function itCanConvertDateTimeStringToOtherTimezone()
    {
        $dateTime = Carbon::parse('2021-05-02 12:43:31', 'America/New_York');
        $dateTimeInOtherTimezone = dateTimeToOtherTimezone($dateTime->toIso8601String(), 'Australia/Adelaide');

        $this->assertEquals($dateTime, $dateTimeInOtherTimezone);
        $this->assertEquals('Australia/Adelaide', $dateTimeInOtherTimezone->getTimezone()->getName());
    }

    /** @test */
    public function itConvertsNullDateTimeToNull()
    {
        $this->assertNull(dateTimeToOtherTimezone(null));
    }
}
