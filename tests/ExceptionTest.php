<?php

namespace Realodix\Timezone\Test;

use PHPUnit\Framework\TestCase;
use Realodix\Timezone\Timezone;

class ExceptionTest extends TestCase
{
    protected Timezone $tz;

    protected function setUp(): void
    {
        $this->tz = new Timezone;
    }

    /**
     * This test should throw an exception when an invalid timezone is passed
     * to the toSelectBox method.
     */
    public function testToSelectBoxThrowsExceptionForInvalidTimezone(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid timezone: New_York');

        $this->tz->toSelectBox('timezone_default', 'New_York');
    }

    /**
     * This test should throw an exception when a timezone is selected that is
     * not within the filter scope.
     */
    public function testThrowsExceptionForTimezoneOutsideFilterScope(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Timezone Australia/Melbourne is not within the specified groups: America, Asia',
        );

        $this->tz
            ->onlyGroups(['Asia', 'America'])
            ->toSelectBox('timezone_default', 'Australia/Melbourne');
    }

    /**
     * This test should throw an exception when an invalid group is passed
     */
    public function testThrowsExceptionForInvalidGroupOnOnlyGroups(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid groups: Americaa');

        $this->tz->onlyGroups(['Americaa']);
    }

    public function testThrowsExceptionForInvalidGroupOnExcludeGroups(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid groups: Americaa');

        $this->tz->excludeGroups(['Americaa']);
    }
}
