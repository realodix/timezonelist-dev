<?php

namespace Realodix\Timezone\Test;

use PHPUnit\Framework\Attributes as PHPUnit;
use PHPUnit\Framework\TestCase;
use Realodix\Timezone\Util;

class UtilityTest extends TestCase
{
    #[PHPUnit\TestWith(['2022-02-01 18:00:00', '2022-02-01 13:00:00', 'America/New_York', 'Europe/London'])]
    #[PHPUnit\TestWith(['2022-02-01 13:00:00', '2022-02-01 13:00:00', 'America/New_York', 'America/New_York'])]
    public function testConvertTimezone($expected, $timestamp, $from, $to)
    {
        $result = Util::convertTimezone($timestamp, $from, $to, 'Y-m-d H:i:s');
        $this->assertEquals($expected, $result);
    }

    #[PHPUnit\TestWith(['invalid timestamp', 'America/New_York', 'Europe/London'])]
    #[PHPUnit\TestWith(['2022-02-01 13:00:00', 'Invalid/Timezone', 'Europe/London'])]
    #[PHPUnit\TestWith(['2022-02-01 13:00:00', 'America/New_York', 'Invalid/Timezone'])]
    public function testConvertTimezoneWithInvalidData($timestamp, $from, $to)
    {
        $this->expectException(\DateException::class);
        Util::convertTimezone($timestamp, $from, $to, 'Y-m-d H:i:s');
    }

    public function testConvertCustomFormat()
    {
        $timestamp = '2022-02-01 13:00:00';
        $from = 'America/New_York';
        $to = 'Europe/London';
        $format = 'Y-m-d H:i A';
        $expected = '2022-02-01 18:00 PM';
        $result = Util::convertTimezone($timestamp, $from, $to, $format);
        $this->assertEquals($expected, $result);
    }

    #[PHPUnit\TestWith(['America', 'America/New_York'])]
    #[PHPUnit\TestWith(['America', 'America/Argentina/Buenos_Aires'])]
    #[PHPUnit\TestWith(['UTC', 'UTC'])]
    public function testExtractContinent($expected, $timezoneId)
    {
        $result = Util::extractContinent($timezoneId);
        $this->assertEquals($expected, $result);
    }

    #[PHPUnit\TestWith(['New_York', 'America/New_York'])]
    #[PHPUnit\TestWith(['Argentina/Buenos_Aires', 'America/Argentina/Buenos_Aires'])]
    #[PHPUnit\TestWith(['UTC', 'UTC'])]
    public function testExtractLocation($expected, $timezoneId)
    {
        $result = Util::extractLocation($timezoneId);
        $this->assertEquals($expected, $result);
    }

    #[PHPUnit\TestWith(['+0', '+00:00'])]
    #[PHPUnit\TestWith(['+1', '+01:00'])]
    #[PHPUnit\TestWith(['+8:45', '+08:45'])]
    #[PHPUnit\TestWith(['+12', '+12:00'])]
    #[PHPUnit\TestWith(['-1', '-01:00'])]
    #[PHPUnit\TestWith(['-12', '-12:00'])]
    #[PHPUnit\TestWith(['-0', '-00:00'])]
    #[PHPUnit\TestWith(['invalid offset', 'invalid offset'])]
    public function testShortOffset($expected, $offset)
    {
        $this->assertEquals($expected, Util::shortOffset($offset));
    }

    public function testIsTimezoneValid()
    {
        $timezone = 'America/New_York';
        $this->assertTrue(Util::isTimezone($timezone));
    }

    public function testIsTimezoneInvalid()
    {
        $timezone = 'Invalid/Timezone';
        $this->assertFalse(Util::isTimezone($timezone));
    }
}
