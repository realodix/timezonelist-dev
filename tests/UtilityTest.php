<?php

namespace Realodix\Timezone\Test;

use PHPUnit\Framework\TestCase;
use Realodix\Timezone\Utility;

class UtilityTest extends TestCase
{
    public function testConvertValidTimezone()
    {
        $timestamp = '2022-02-01 13:00:00';
        $from = 'America/New_York';
        $to = 'Europe/London';
        $format = 'Y-m-d H:i:s';
        $expected = '2022-02-01 18:00:00';
        $result = Utility::convertTimezone($timestamp, $from, $to, $format);
        $this->assertEquals($expected, $result);
    }

    public function testConvertInvalidFromTimezone()
    {
        $timestamp = '2022-02-01 13:00:00';
        $from = 'Invalid/Timezone';
        $to = 'Europe/London';
        $format = 'Y-m-d H:i:s';
        $this->expectException(\DateInvalidTimeZoneException::class);
        Utility::convertTimezone($timestamp, $from, $to, $format);
    }

    public function testConvertInvalidToTimezone()
    {
        $timestamp = '2022-02-01 13:00:00';
        $from = 'America/New_York';
        $to = 'Invalid/Timezone';
        $format = 'Y-m-d H:i:s';
        $this->expectException(\DateInvalidTimeZoneException::class);
        Utility::convertTimezone($timestamp, $from, $to, $format);
    }

    public function testConvertInvalidTimestamp()
    {
        $timestamp = ' invalid timestamp ';
        $from = 'America/New_York';
        $to = 'Europe/London';
        $format = 'Y-m-d H:i:s';
        $this->expectException(\DateMalformedStringException::class);
        Utility::convertTimezone($timestamp, $from, $to, $format);
    }

    public function testConvertSameTimezone()
    {
        $timestamp = '2022-02-01 13:00:00';
        $from = 'America/New_York';
        $to = 'America/New_York';
        $format = 'Y-m-d H:i:s';
        $expected = '2022-02-01 13:00:00';
        $result = Utility::convertTimezone($timestamp, $from, $to, $format);
        $this->assertEquals($expected, $result);
    }

    public function testConvertCustomFormat()
    {
        $timestamp = '2022-02-01 13:00:00';
        $from = 'America/New_York';
        $to = 'Europe/London';
        $format = 'Y-m-d H:i A';
        $expected = '2022-02-01 18:00 PM';
        $result = Utility::convertTimezone($timestamp, $from, $to, $format);
        $this->assertEquals($expected, $result);
    }

    public function testShortOffset_plus00()
    {
        $offset = '+00:00';
        $expected = '+0';
        $this->assertEquals($expected, Utility::shortOffset($offset));
    }

    public function testShortOffset_minus00()
    {
        $offset = '-00:00';
        $expected = '-0';
        $this->assertEquals($expected, Utility::shortOffset($offset));
    }

    public function testShortOffset_plus01()
    {
        $offset = '+01:00';
        $expected = '+1';
        $this->assertEquals($expected, Utility::shortOffset($offset));
    }

    public function testShortOffset_minus01()
    {
        $offset = '-01:00';
        $expected = '-1';
        $this->assertEquals($expected, Utility::shortOffset($offset));
    }

    public function testShortOffset_plus12()
    {
        $offset = '+12:00';
        $expected = '+12';
        $this->assertEquals($expected, Utility::shortOffset($offset));
    }

    public function testShortOffset_minus12()
    {
        $offset = '-12:00';
        $expected = '-12';
        $this->assertEquals($expected, Utility::shortOffset($offset));
    }

    public function testShortOffset_plus845()
    {
        $offset = '+08:45';
        $expected = '+8:45';
        $this->assertEquals($expected, Utility::shortOffset($offset));
    }

    public function testShortOffset_invalidFormat()
    {
        $offset = ' invalid offset ';
        $expected = ' invalid offset ';
        $this->assertEquals($expected, Utility::shortOffset($offset));
    }

    public function testIsTimezoneValid()
    {
        $timezone = 'America/New_York';
        $this->assertTrue(Utility::isTimezone($timezone));
    }

    public function testIsTimezoneInvalid()
    {
        $timezone = 'Invalid/Timezone';
        $this->assertFalse(Utility::isTimezone($timezone));
    }
}
