<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes as PHPUnit;
use PHPUnit\Framework\TestCase;
use Realodix\Timezonelist\Timezonelist;

class ToArrayTest extends TestCase
{
    private Timezonelist $tzList;

    protected function setUp(): void
    {
        $this->tzList = new Timezonelist;
    }

    #[PHPUnit\Test]
    public function noGroup_noFilter()
    {
        $result = $this->tzList
            ->splitGroup(false)
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
        $result = $this->tzList->toArray(); // Default: splitGroup = true

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);

        // Check if the top-level keys are continent names
        foreach ($result as $continent => $timezones) {
            $this->assertIsString($continent);
            $this->assertIsArray($timezones);

            foreach ($timezones as $timezone => $formattedTimezone) {
                $this->assertIsString($timezone);
                $this->assertIsString($formattedTimezone);
                $this->assertStringContainsString('UTC', $formattedTimezone);
            }
        }

        $this->assertArrayHasKey('America', $result);
        $this->assertArrayNotHasKey('America/New_York', $result);
    }

    #[PHPUnit\Test]
    public function noGroup_withFilter()
    {
        $result = $this->tzList
            ->splitGroup(false)
            ->onlyGroups(['Asia', 'Europe'])
            ->toArray();

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);

        foreach ($result as $timezone => $formattedTimezone) {
            $this->assertIsString($timezone);
            $this->assertIsString($formattedTimezone);
            $this->assertStringContainsString('UTC', $formattedTimezone);
        }

        // Assert that only timezones from Asia and Europe are present. This is tricky without
        // knowing *exactly* what timezones are returned a better approach might be to count
        // the number of returned values for each continent.
        $asiaCount = 0;
        $europeCount = 0;
        foreach (array_keys($result) as $timezone) {
            if (str_starts_with($timezone, 'Asia/')) {
                $asiaCount++;
            }
            if (str_starts_with($timezone, 'Europe/')) {
                $europeCount++;
            }
        }
        $this->assertGreaterThan(0, $asiaCount, 'No Asia timezones found');
        $this->assertGreaterThan(0, $europeCount, 'No Europe timezones found');
        $this->assertArrayNotHasKey('America/New_York', $result);
        $this->assertArrayNotHasKey('America', $result);
    }

    #[PHPUnit\Test]
    public function withGroup_withFilter()
    {
        $result = $this->tzList
            ->onlyGroups(['Asia', 'Europe'])
            ->toArray();

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);

        $this->assertArrayHasKey('Asia', $result);
        $this->assertArrayHasKey('Europe', $result);
        $this->assertArrayNotHasKey('America', $result);
        $this->assertArrayNotHasKey('America/New_York', $result);

        foreach ($result['Asia'] as $timezone => $formattedTimezone) {
            $this->assertIsString($timezone);
            $this->assertIsString($formattedTimezone);
            $this->assertStringContainsString('UTC', $formattedTimezone);
        }
    }

    #[PHPUnit\Test]
    public function excludeGroup()
    {
        $result = $this->tzList
            ->excludeGroups(['Asia', 'Europe'])
            ->toArray();

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);

        $this->assertArrayNotHasKey('Asia', $result);
        $this->assertArrayNotHasKey('Europe', $result);
        $this->assertArrayHasKey('America', $result); // Should not be filtered out
    }
}
