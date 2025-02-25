<?php

namespace Realodix\Timezonelist;

use Realodix\Timezonelist\Exceptions\OutOfScopeTimezoneException;

class Timezonelist
{
    const HTML_WHITESPACE = '&nbsp;';
    const GROUP_GENERAL = 'General';

    /**
     * Array of continents and their corresponding DateTimeZone constants.
     *
     * @var array
     */
    const CONTINENTS = [
        'Africa'     => \DateTimeZone::AFRICA,
        'America'    => \DateTimeZone::AMERICA,
        'Antarctica' => \DateTimeZone::ANTARCTICA,
        'Arctic'     => \DateTimeZone::ARCTIC,
        'Asia'       => \DateTimeZone::ASIA,
        'Atlantic'   => \DateTimeZone::ATLANTIC,
        'Australia'  => \DateTimeZone::AUSTRALIA,
        'Europe'     => \DateTimeZone::EUROPE,
        'Indian'     => \DateTimeZone::INDIAN,
        'Pacific'    => \DateTimeZone::PACIFIC,
    ];

    /**
     * List of groups to include/exclude in the timezone list. An empty array
     * indicates all groups.
     */
    protected array $groups = [];

    /**
     * Whether to group timezones by continent
     */
    protected bool $splitGroup = true;

    /**
     * Whether to display the timezone offset
     */
    protected bool $showOffset = true;

    /**
     * Creates an HTML select box of timezones
     *
     * @param string $name The name attribute of the select tag
     * @param string|null $selected The value of the option to be pre-selected
     * @param array|null $attrs Additional HTML attributes
     */
    public function toSelectBox(string $name, ?string $selected = null, ?array $attrs = null): string
    {
        $withGroup = $this->splitGroup;

        if ($selected) {
            $this->validateTimezone($selected);
        }

        $attributes = '';
        if (!empty($attrs)) {
            foreach ($attrs as $attr_name => $attr_value) {
                $attributes .= ' '.$attr_name.'="'.$attr_value.'"';
            }
        }

        $output = '<select name="'.$name.'"'.$attributes.'>';

        $options = [];

        if ($this->includeGeneral()) {
            if ($withGroup) {
                $options[] = '<optgroup label="General">';
            }

            $timezone = 'UTC';
            $options[] = $this->makeOptionTag(
                $this->formatTimezone($timezone),
                $timezone,
                ($selected === $timezone),
            );

            if ($withGroup) {
                $options[] = '</optgroup>';
            }
        }

        foreach ($this->loadContinents() as $continent => $mask) {
            $timezones = \DateTimeZone::listIdentifiers($mask);

            if ($withGroup) {
                $options[] = '<optgroup label="'.$continent.'">';
            }

            foreach ($timezones as $timezone) {
                $continent = is_int($continent) ? null : $continent;
                $cutOffContinent = $withGroup ? $continent : null;

                $options[] = $this->makeOptionTag(
                    $this->formatTimezone($timezone, $cutOffContinent),
                    $timezone,
                    ($selected === $timezone),
                );
            }

            if ($withGroup) {
                $options[] = '</optgroup>';
            }
        }

        // Join the options array into a single string
        $output .= implode('', $options);
        $output .= '</select>';

        return $output;
    }

    /**
     * Generates an array of timezones, with optional grouping by continent
     *
     * If $this->splitGroup is true, the array will be a multidimensional array with
     * the first key being the continent. Otherwise, the array will be a flat array
     * of timezones.
     */
    public function toArray(): array
    {
        $list = [];

        // If not splitting time zones by continental group
        if (!$this->splitGroup) {
            if ($this->includeGeneral()) {
                $timezone = 'UTC';
                $list[$timezone] = $this->formatTimezone($timezone);
            }

            foreach ($this->loadContinents() as $continent => $mask) {
                $timezones = \DateTimeZone::listIdentifiers($mask);

                foreach ($timezones as $timezone) {
                    $list[$timezone] = $this->formatTimezone($timezone);
                }
            }

            return $list;
        }

        // If splitting time zones by continental group
        if ($this->includeGeneral()) {
            $timezone = 'UTC';
            $list[self::GROUP_GENERAL][$timezone] = $this->formatTimezone($timezone);
        }

        foreach ($this->loadContinents() as $continent => $mask) {
            $timezones = \DateTimeZone::listIdentifiers($mask);

            foreach ($timezones as $timezone) {
                $list[$continent][$timezone] = $this->formatTimezone($timezone, $continent);
            }
        }

        return $list;
    }

    /**
     * Sets the filter to include only the specified continent/group names
     *
     * @param array<string> $groups The continent/group names to include.
     * @return $this
     */
    public function onlyGroups(array $groups)
    {
        $this->groups = $this->processGroupName($groups);

        return $this;
    }

    /**
     * Sets the filter to exclude the specified continent/group names
     *
     * @param array<string> $groups The continent/group names to exclude
     * @return $this
     */
    public function excludeGroups(array $groups)
    {
        $groups = $this->processGroupName($groups);
        $this->groups = array_values(array_diff(array_keys(self::CONTINENTS), $groups));

        if (!in_array(self::GROUP_GENERAL, $groups)) {
            $this->groups[] = self::GROUP_GENERAL;
        }

        return $this;
    }

    /**
     * Sets whether to split the timezone list into groups (continents)
     *
     * @param bool $value Whether to split into groups
     * @return $this
     */
    public function splitGroup(bool $value = true)
    {
        $this->splitGroup = $value;

        return $this;
    }

    /**
     * Sets whether to display the timezone offset
     *
     * @param bool $value Whether to display the offset
     * @return $this
     */
    public function showOffset(bool $value = true)
    {
        $this->showOffset = $value;

        return $this;
    }

    /**
     * Generate HTML <option> tag
     *
     * @param string $display The text to display for the option
     * @param string $value The value of the option.
     * @param bool $selected Whether the option should be selected
     */
    protected function makeOptionTag(string $display, string $value, bool $selected): string
    {
        $attrs = $selected ? ' selected="selected"' : '';

        return "<option value=\"{$value}\"{$attrs}>{$display}</option>";
    }

    /**
     * Checks if the "General" timezone group should be included based on
     * the current filter.
     */
    protected function includeGeneral(): bool
    {
        return empty($this->groups) || in_array(self::GROUP_GENERAL, $this->groups);
    }

    /**
     * Loads the filtered list of continents based on the current group filter.
     */
    protected function loadContinents(): array
    {
        if (empty($this->groups)) {
            return self::CONTINENTS;
        }

        return array_filter(
            self::CONTINENTS,
            fn($key) => in_array($key, $this->groups),
            ARRAY_FILTER_USE_KEY,
        );
    }

    /**
     * Formats a timezone name for display, optionally including the continent name and offset.
     *
     * @param string $timezone The timezone name to format
     * @param string|null $cutOffContinent The continent name to remove from the timezone name (if applicable)
     */
    protected function formatTimezone(string $timezone, ?string $cutOffContinent = null): string
    {
        $displayedTz = empty($cutOffContinent) ? $timezone : substr($timezone, strlen($cutOffContinent) + 1);
        $normalizedTz = $this->normalizeTimezone($displayedTz);

        if (!$this->showOffset) {
            return $normalizedTz;
        }

        $offset = $this->getOffset($timezone);
        $separator = str_repeat(self::HTML_WHITESPACE, 3);

        return "(UTC{$offset})".$separator.$normalizedTz;
    }

    /**
     * Normalizes a timezone name
     *
     * @param string $timezone The timezone name to normalize
     */
    protected function normalizeTimezone(string $timezone): string
    {
        $search = ['St_', '/', '_'];
        $replace = ['St. ', ' / ', ' '];

        return str_replace($search, $replace, $timezone);
    }

    /**
     * Get the timezone offset in ISO 8601 format (e.g., "+00:00")
     *
     * @param string $timezone The timezone name
     */
    protected function getOffset(string $timezone): string
    {
        $time = new \DateTime('', new \DateTimeZone($timezone));

        return $time->format('P');
    }

    /**
     * Process an array of continent names
     *
     * Converts group names to ucfirst format and ensures they are valid continents.
     *
     * @param array<string> $groups
     * @return array<string>
     */
    protected function processGroupName(array $groups): array
    {
        return array_map(fn($group) => ucfirst(strtolower($group)), $groups);
    }

    /**
     * @param string $timezone The timezone name to validate
     * @return void
     *
     * @throws \InvalidArgumentException When the timezone is invalid
     * @throws OutOfScopeTimezoneException When the timezone is not within the specified groups
     */
    protected function validateTimezone(string $timezone)
    {
        if (!in_array($timezone, \DateTimeZone::listIdentifiers())) {
            throw new \InvalidArgumentException('Invalid timezone: '.$timezone);
        }
        // Check if a filter is applied and if the timezone is within the filter
        if (!empty($this->groups)) {
            $timezoneContinent = explode('/', $timezone)[0];
            if (!in_array($timezoneContinent, $this->groups) && $timezone !== 'UTC') {
                throw new OutOfScopeTimezoneException($timezone, $this->groups);
            }
        }
    }
}
