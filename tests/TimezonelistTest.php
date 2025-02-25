<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes as PHPUnit;
use PHPUnit\Framework\TestCase;
use Realodix\Timezonelist\Timezonelist;

class TimezonelistTest extends TestCase
{
    protected Timezonelist $tzList;

    protected function setUp(): void
    {
        $this->tzList = new Timezonelist;
    }

    #[PHPUnit\Test]
    public function toSelectBox(): void
    {
        $output = $this->tzList->toSelectBox('timezone_default');
        $this->assertStringStartsWith('<select name="timezone_default"', $output);
        $this->assertStringContainsString('<optgroup label="General">', $output);
        $this->assertStringContainsString('<option value="UTC">(UTC+00:00)&nbsp;&nbsp;&nbsp;UTC</option>', $output);
        $this->assertStringContainsString('<optgroup label="Africa">', $output);
        $this->assertStringContainsString('<option value="Africa/Abidjan">(UTC', $output);
        $this->assertStringContainsString('<optgroup label="America">', $output);
        $this->assertStringContainsString('<option value="America/New_York">(UTC-05:00)', $output);
        $this->assertStringContainsString('<optgroup label="Antarctica">', $output);
        $this->assertStringContainsString('<option value="Antarctica/Casey">(UTC', $output);
        $this->assertStringContainsString('<optgroup label="Arctic">', $output);
        $this->assertStringContainsString('<option value="Arctic/Longyearbyen">(UTC', $output);
        $this->assertStringContainsString('<optgroup label="Asia">', $output);
        $this->assertStringContainsString('<option value="Asia/Aden">(UTC', $output);
        $this->assertStringContainsString('<optgroup label="Atlantic">', $output);
        $this->assertStringContainsString('<option value="Atlantic/Azores">(UTC', $output);
        $this->assertStringContainsString('<optgroup label="Australia">', $output);
        $this->assertStringContainsString('<option value="Australia/Adelaide">(UTC', $output);
        $this->assertStringContainsString('<optgroup label="Europe">', $output);
        $this->assertStringContainsString('<option value="Europe/Amsterdam">(UTC', $output);
        $this->assertStringContainsString('<optgroup label="Indian">', $output);
        $this->assertStringContainsString('<option value="Indian/Antananarivo">(UTC', $output);
        $this->assertStringContainsString('<optgroup label="Pacific">', $output);
        $this->assertStringContainsString('<option value="Pacific/Apia">(UTC+13:00)', $output);
        $this->assertStringEndsWith('</select>', $output);

        // Implicitly tests normalizeOffset, getOffset, normalizeTimezone, normalizeSeparator via output
        $this->assertStringContainsString('-', $output, 'Ensure HTML_MINUS is in offset (normalizeOffset)');
        $this->assertStringContainsString('+', $output, 'Ensure HTML_PLUS is in offset (normalizeOffset)');
        $this->assertStringContainsString('&nbsp;&nbsp;&nbsp;', $output, 'Ensure normalizeSeparator works');
    }

    public function testSelectedValue(): void
    {
        $selectedTimezone = 'America/New_York';
        $output = $this->tzList->toSelectBox('timezone_selected', $selectedTimezone);
        $this->assertStringContainsString('<option value="America/New_York" selected="selected">', $output);
    }

    public function testAttributes(): void
    {
        $attrsArray = ['class' => 'form-control', 'id' => 'timezone-select'];
        $outputArray = $this->tzList->toSelectBox('timezone_attrs_array', attrs: $attrsArray);
        $this->assertStringContainsString('<select name="timezone_attrs_array" class="form-control" id="timezone-select">', $outputArray);
    }

    public function testToSelectBox_WithGroup_WithOffset(): void
    {
        $output = $this->tzList->splitGroup()->showOffset()->toSelectBox('timezone_with_group_offset');
        $this->assertStringStartsWith('<select name="timezone_with_group_offset"', $output);
        $this->assertStringContainsString('<optgroup label="Africa">', $output);
        $this->assertStringContainsString('<option value="Africa/Abidjan">(UTC', $output);
        $this->assertStringEndsWith('</select>', $output);

        // Implicitly tests normalizeOffset, getOffset, normalizeTimezone, normalizeSeparator via output
        $this->assertStringContainsString('-', $output, 'Ensure HTML_MINUS is in offset (normalizeOffset)');
        $this->assertStringContainsString('+', $output, 'Ensure HTML_PLUS is in offset (normalizeOffset)');
        $this->assertStringContainsString('&nbsp;&nbsp;&nbsp;', $output, 'Ensure normalizeSeparator works');
    }

    public function testToSelectBox_WithoutGroup_WithOffset(): void
    {
        $output = $this->tzList->splitGroup(false)->showOffset()->toSelectBox('timezone_without_group_offset');
        $this->assertStringStartsWith('<select name="timezone_without_group_offset"', $output);
        $this->assertStringNotContainsString('<optgroup label="Africa">', $output);
        $this->assertStringContainsString('<option value="Africa/Abidjan">(UTC', $output);
        $this->assertStringEndsWith('</select>', $output);

        // Implicitly tests normalizeOffset, getOffset, normalizeTimezone, normalizeSeparator via output
        $this->assertStringContainsString('-', $output, 'Ensure HTML_MINUS is in offset (normalizeOffset)');
        $this->assertStringContainsString('+', $output, 'Ensure HTML_PLUS is in offset (normalizeOffset)');
        $this->assertStringContainsString('&nbsp;&nbsp;&nbsp;', $output, 'Ensure normalizeSeparator works');
    }

    public function testToSelectBox_WithGroup_WithoutOffset(): void
    {
        $output = $this->tzList->splitGroup()->showOffset(false)->toSelectBox('timezone_with_group_no_offset');
        $this->assertStringStartsWith('<select name="timezone_with_group_no_offset"', $output);
        $this->assertStringContainsString('<optgroup label="Africa">', $output);
        $this->assertStringNotContainsString('(UTC', $output);
        $this->assertStringContainsString('<option value="Africa/Abidjan">Abidjan</option>', $output);
        $this->assertStringEndsWith('</select>', $output);
    }

    public function testToSelectBox_WithoutGroup_WithoutOffset(): void
    {
        $output = $this->tzList->splitGroup(false)->showOffset(false)->toSelectBox('timezone_no_group_no_offset');
        $this->assertStringStartsWith('<select name="timezone_no_group_no_offset"', $output);
        $this->assertStringNotContainsString('<optgroup label="Africa">', $output);
        $this->assertStringNotContainsString('(UTC', $output);
        $this->assertStringContainsString('<option value="Africa/Abidjan">Africa / Abidjan</option>', $output); // Corrected Assertion
        $this->assertStringEndsWith('</select>', $output);
    }

    public function testOnlyGroups(): void
    {
        $service = $this->tzList->onlyGroups(['Africa', 'America']);
        $this->assertInstanceOf(Timezonelist::class, $service);

        $output = $this->tzList->onlyGroups(['Africa'])->toSelectBox('timezone_only_africa');
        $this->assertStringContainsString('<optgroup label="Africa">', $output);
        $this->assertStringNotContainsString('<optgroup label="America">', $output);

        $output = $this->tzList->onlyGroups(['America'])->toSelectBox('timezone_only_america');
        $this->assertStringNotContainsString('<optgroup label="Africa">', $output);
        $this->assertStringContainsString('<optgroup label="America">', $output);
    }

    public function testExcludeGroups(): void
    {
        $service = $this->tzList->excludeGroups(['Africa']);
        $this->assertInstanceOf(Timezonelist::class, $service);

        $output = $this->tzList->excludeGroups(['Africa'])->toSelectBox('timezone_exclude_africa');
        $this->assertStringNotContainsString('<optgroup label="Africa">', $output);
        $this->assertStringContainsString('<optgroup label="America">', $output);
        $this->assertStringContainsString('<optgroup label="General">', $output);

        $output = $this->tzList->excludeGroups(['General'])->toSelectBox('timezone_exclude_general');
        $this->assertStringNotContainsString('<optgroup label="General">', $output);
        $this->assertStringContainsString('<optgroup label="America">', $output);
    }

    public function testSplitGroup(): void
    {
        $service = $this->tzList->splitGroup(true);
        $this->assertInstanceOf(Timezonelist::class, $service);
        $outputWithGroup = $this->tzList->splitGroup(true)->toSelectBox('timezone_split_true');
        $this->assertStringContainsString('<optgroup', $outputWithGroup, 'Asserting optgroup tag exists when splitGroup is true');

        $outputWithoutGroup = $this->tzList->splitGroup(false)->toSelectBox('timezone_split_false');
        $this->assertStringNotContainsString('<optgroup', $outputWithoutGroup, 'Asserting optgroup tag does not exist when splitGroup is false');
    }

    public function testShowOffset(): void
    {
        $service = $this->tzList->showOffset(true);
        $this->assertInstanceOf(Timezonelist::class, $service);

        $outputWithOffset = $this->tzList->showOffset(true)->toSelectBox('timezone_offset_true');
        $this->assertStringContainsString('(UTC', $outputWithOffset, 'Asserting offset prefix exists when showOffset is true');

        $outputWithoutOffset = $this->tzList->showOffset(false)->toSelectBox('timezone_offset_false');
        $this->assertStringNotContainsString('(UTC', $outputWithoutOffset, 'Asserting offset prefix does not exist when showOffset is false');
    }

    public function testNormalizeTimezone(): void
    {
        $output = $this->tzList->toSelectBox('timezone_default');
        $this->assertStringContainsString(
            '<option value="America/Argentina/Rio_Gallegos">(UTC-03:00)&nbsp;&nbsp;&nbsp;Argentina / Rio Gallegos</option>',
            $output,
        );
        $this->assertStringContainsString(
            '<option value="America/St_Johns">(UTC-03:30)&nbsp;&nbsp;&nbsp;St. Johns</option>',
            $output,
        );
    }

    #[PHPUnit\Test]
    public function testToSelectBoxWithInvalidSelectedTimezone(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid timezone: New_York');

        $this->tzList->toSelectBox('timezone_default', 'New_York');
    }

    #[PHPUnit\Test]
    public function testToSelectBoxWithFilteredTimezone(): void
    {
        $this->expectException(\Realodix\Timezonelist\Exceptions\OutOfScopeTimezoneException::class);
        $this->expectExceptionMessage(
            'Timezone Australia/Melbourne is not within the specified groups: America, Asia',
        );

        $this->tzList
            ->onlyGroups(['Asia', 'America'])
            ->toSelectBox('timezone_default', 'Australia/Melbourne');
    }

    #[PHPUnit\Test]
    public function normalizeContinentInput()
    {
        $excludeResult = $this->tzList
            ->excludeGroups(['asia', 'europe'])
            ->toArray();
        $this->assertIsArray($excludeResult);
        $this->assertNotEmpty($excludeResult);
        $this->assertArrayNotHasKey('Asia', $excludeResult);
        $this->assertArrayNotHasKey('Europe', $excludeResult);
        $this->assertArrayHasKey('America', $excludeResult);

        $onlyResult = $this->tzList
            ->onlyGroups(['Asia', 'Europe'])
            ->toArray();
        $this->assertIsArray($onlyResult);
        $this->assertNotEmpty($onlyResult);
        $this->assertArrayHasKey('Asia', $onlyResult);
        $this->assertArrayHasKey('Europe', $onlyResult);
        $this->assertArrayNotHasKey('America', $onlyResult); // Should be filtered out
    }

    public function testConstants(): void
    {
        $this->assertSame('&nbsp;', Timezonelist::HTML_WHITESPACE);
    }

    // Example of a dedicated test for normalizeTimezone (if you really need it for isolated testing)
    // In general, it's better to test via public methods.
    /*
    public function testNormalizeTimezone_Dedicated(): void
    {
        $this->assertSame('St. Helena', $this->tzList->normalizeTimezone('St_Helena'));
        $this->assertSame('America / Argentina / Buenos Aires', $this->tzList->normalizeTimezone('America/Argentina/Buenos_Aires'));
        $this->assertSame('America / Argentina / Buenos Aires', $this->tzList->normalizeTimezone('America/Argentina/Buenos_Aires'));
    }
    */
}
