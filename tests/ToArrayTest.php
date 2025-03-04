<?php

namespace Realodix\Timezone\Test;

use PHPUnit\Framework\Attributes as PHPUnit;
use PHPUnit\Framework\TestCase;
use Realodix\Timezone\Timezone;

class ToArrayTest extends TestCase
{
    private Timezone $tz;

    protected function setUp(): void
    {
        $this->tz = new Timezone;
    }

    #[PHPUnit\Test]
    public function noGroup_noFilter()
    {
        $result = $this->tz
            ->flatten()
            ->toArray();

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);

        // Check if keys are timezone strings and values are formatted timezone strings
        foreach ($result as $timezone => $formattedTimezone) {
            $this->assertIsString($timezone);
            $this->assertIsString($formattedTimezone);
            $this->assertStringContainsString('(UTC', $formattedTimezone);
        }

        $this->assertArrayHasKey('America/New_York', $result);
        $this->assertArrayNotHasKey('America', $result);
    }

    #[PHPUnit\Test]
    public function withGroup_noFilter()
    {
        $result = $this->tz->toArray();

        $this->assertIsArray($result);
        $this->assertSame(
            $this->withGroup_noFilter_baseline(),
            collect($result)->map(fn($item) => array_slice($item, 0, 1))->toArray(),
        );
    }

    protected function withGroup_noFilter_baseline(): array
    {
        return [
            'General'    => ['UTC' => '(UTC+00:00) UTC'],
            'Africa'     => ['Africa/Abidjan' => '(UTC+00:00) Abidjan'],
            'America'    => ['America/Adak' => '(UTC-10:00) Adak'],
            'Antarctica' => ['Antarctica/Casey' => '(UTC+08:00) Casey'],
            'Arctic'     => ['Arctic/Longyearbyen' => '(UTC+01:00) Longyearbyen'],
            'Asia'       => ['Asia/Aden' => '(UTC+03:00) Aden'],
            'Atlantic'   => ['Atlantic/Azores' => '(UTC-01:00) Azores'],
            'Australia'  => ['Australia/Adelaide' => '(UTC+10:30) Adelaide'],
            'Europe'     => ['Europe/Amsterdam' => '(UTC+01:00) Amsterdam'],
            'Indian'     => ['Indian/Antananarivo' => '(UTC+03:00) Antananarivo'],
            'Pacific'    => ['Pacific/Apia' => '(UTC+13:00) Apia'],
        ];
    }

    #[PHPUnit\Test]
    public function noGroup_withFilter()
    {
        $result = $this->tz
            ->flatten()
            ->onlyGroups(['General', 'Arctic'])
            ->toArray();

        $this->assertIsArray($result);
        $this->assertSame(
            [
                'UTC' => '(UTC+00:00) UTC',
                'Arctic/Longyearbyen' => '(UTC+01:00) Arctic / Longyearbyen',
            ],
            $result,
        );
    }

    #[PHPUnit\Test]
    public function withGroup_withFilter()
    {
        $result = $this->tz
            ->onlyGroups(['General', 'Arctic', 'Atlantic'])
            ->toArray();

        $this->assertSame($this->filter_baseline(), $result);
    }

    #[PHPUnit\Test]
    public function excludeGroup()
    {
        $result = $this->tz
            ->excludeGroups([
                'Africa', 'America', 'Antarctica', 'Asia', 'Australia',
                'Europe', 'Indian', 'Pacific',
            ])->toArray();

        $this->assertSame($this->filter_baseline(), $result);
    }

    protected function filter_baseline(): array
    {
        return [
            'General' => [
                'UTC' => '(UTC+00:00) UTC',
            ],
            'Arctic' => [
                'Arctic/Longyearbyen' => '(UTC+01:00) Longyearbyen',
            ],
            'Atlantic' => [
                'Atlantic/Azores' => '(UTC-01:00) Azores',
                'Atlantic/Bermuda' => '(UTC-04:00) Bermuda',
                'Atlantic/Canary' => '(UTC+00:00) Canary',
                'Atlantic/Cape_Verde' => '(UTC-01:00) Cape Verde',
                'Atlantic/Faroe' => '(UTC+00:00) Faroe',
                'Atlantic/Madeira' => '(UTC+00:00) Madeira',
                'Atlantic/Reykjavik' => '(UTC+00:00) Reykjavik',
                'Atlantic/South_Georgia' => '(UTC-02:00) South Georgia',
                'Atlantic/St_Helena' => '(UTC+00:00) St. Helena',
                'Atlantic/Stanley' => '(UTC-03:00) Stanley',
            ],
        ];
    }
}
