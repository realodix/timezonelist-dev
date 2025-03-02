<?php

namespace Realodix\Timezone\Test;

use PHPUnit\Framework\Attributes as PHPUnit;
use PHPUnit\Framework\TestCase;
use Realodix\Timezone\Exceptions\InvalidGroupException;
use Realodix\Timezone\Exceptions\TimezoneOutOfScopeException;
use Realodix\Timezone\Timezone;

class ExceptionTest extends TestCase
{
    protected Timezone $tz;

    protected function setUp(): void
    {
        $this->tz = new Timezone;
    }

    /**
     * This test should throw an exception when an invalid group is passed
     */
    public function testThrowsExceptionForInvalidGroupOnOnlyGroups(): void
    {
        $this->expectException(InvalidGroupException::class);
        $this->expectExceptionMessage('Invalid groups: Americaa');

        $this->tz->onlyGroups(['Americaa']);
    }

    public function testThrowsExceptionForInvalidGroupOnExcludeGroups(): void
    {
        $this->expectException(InvalidGroupException::class);
        $this->expectExceptionMessage('Invalid groups: Americaa');

        $this->tz->excludeGroups(['Americaa']);
    }

    /**
     * This test should throw an exception when an invalid timezone is passed
     * to the toSelectBox method.
     */
    public function testToSelectBoxThrowsExceptionForInvalidTimezone(): void
    {
        $this->expectException(\Realodix\Timezone\Exceptions\InvalidTimezoneException::class);
        $this->expectExceptionMessage('Invalid timezone: New_York');

        $this->tz->toSelectBox('timezone_default', 'New_York');
    }

    /**
     * This test should throw an exception when a timezone is selected that is
     * not within the filter scope.
     */
    public function testThrowsExceptionForTimezoneOutsideFilterScope_1(): void
    {
        $tz = $this->tz->onlyGroups(['Asia', 'America']);

        $this->assertIsString($tz->toSelectBox('timezone_default', 'America/Argentina/Buenos_Aires'));

        $this->expectException(TimezoneOutOfScopeException::class);
        $this->expectExceptionMessage(
            'Timezone "Australia/Melbourne" is not within the specified groups: America, Asia',
        );
        $tz->toSelectBox('timezone_default', 'Australia/Melbourne');
    }

    /**
     * This test should throw an exception when a timezone is selected that is
     * not within the filter scope.
     */
    #[PHPUnit\TestWith([['General', 'America']])]
    #[PHPUnit\TestWith([['Asia', 'America']])]
    public function testThrowsExceptionForTimezoneOutsideFilterScope_2(array $groups): void
    {
        $tz = $this->tz->onlyGroups($groups);

        $this->expectException(TimezoneOutOfScopeException::class);
        $tz->toSelectBox('timezone_default', 'Australia/Melbourne');
    }
}
