<?php

namespace Realodix\Timezone;

use Illuminate\Support\Collection;

class Timezone
{
    const HTML_WHITESPACE = '&nbsp;';
    const GROUP_GENERAL = 'General';

    /**
     * Array of continents and their corresponding DateTimeZone constants.
     *
     * @var array<string, int>
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
     *
     * @var list<string>
     */
    protected array $selectedGroups = [];

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
        if ($selected) {
            $this->validateTimezone($selected);
        }

        $attributes = Collection::make($attrs)
            ->map(fn($value, $key) => "{$key}=\"{$value}\"")
            ->implode(' ');

        $options = [];
        if ($this->hasGeneralGroup()) {
            $options[] = $this->splitGroup ? '<optgroup label="General">' : '';
            $options[] = $this->makeOptionTag('UTC', $selected);
            $options[] = $this->splitGroup ? '</optgroup>' : '';
        }

        foreach ($this->loadContinents() as $continent => $mask) {
            $timezones = \DateTimeZone::listIdentifiers($mask);
            $options[] = $this->splitGroup ? '<optgroup label="'.$continent.'">' : '';
            foreach ($timezones as $timezone) {
                $continent = $this->splitGroup ? $continent : null;
                $options[] = $this->makeOptionTag($timezone, $selected, $continent);
            }
            $options[] = $this->splitGroup ? '</optgroup>' : '';
        }

        return "<select name=\"{$name}\" {$attributes}>".implode('', $options).'</select>';
    }

    /**
     * Generates an array of timezones, with optional grouping by continent
     *
     * If $this->splitGroup is true, the array will be a multidimensional array
     * with the first key being the continent. Otherwise, the array will be
     * a flat array of timezones.
     */
    public function toArray(): array
    {
        $list = [];

        if ($this->splitGroup) {
            if ($this->hasGeneralGroup()) {
                $list[self::GROUP_GENERAL]['UTC'] = $this->formatTimezone('UTC', htmlEncode: false);
            }

            foreach ($this->loadContinents() as $continent => $mask) {
                $tzIdentifiers = \DateTimeZone::listIdentifiers($mask);
                foreach ($tzIdentifiers as $timezone) {
                    $list[$continent][$timezone] = $this->formatTimezone($timezone, $continent, false);
                }
            }
        } else {
            if ($this->hasGeneralGroup()) {
                $list['UTC'] = $this->formatTimezone('UTC', htmlEncode: false);
            }

            foreach ($this->loadContinents() as $continent => $mask) {
                $tzIdentifiers = \DateTimeZone::listIdentifiers($mask);
                foreach ($tzIdentifiers as $timezone) {
                    $list[$timezone] = $this->formatTimezone($timezone, htmlEncode: false);
                }
            }
        }

        return $list;
    }

    /**
     * Sets the filter to include only the specified continent/group names
     *
     * @param list<string> $groups The continent/group names to include.
     * @return $this
     */
    public function onlyGroups(array $groups)
    {
        $this->validateGroups($groups);
        $this->selectedGroups = $groups;

        return $this;
    }

    /**
     * Sets the filter to exclude the specified continent/group names
     *
     * @param list<string> $groups The continent/group names to exclude
     * @return $this
     */
    public function excludeGroups(array $groups)
    {
        $this->validateGroups($groups);

        $this->selectedGroups = Collection::make(self::CONTINENTS)
            ->except($groups)->keys()
            ->when(!in_array(self::GROUP_GENERAL, $groups), function ($collection) {
                $collection->push(self::GROUP_GENERAL);
            })->all();

        return $this;
    }

    /**
     * Disables the grouping of timezones by continent
     *
     * This method flattens the timezone list, removing the continental grouping.
     *
     * @return $this
     */
    public function disableGrouping()
    {
        $this->splitGroup = false;

        return $this;
    }

    /**
     * /**
     * Omits the display of timezone offsets
     *
     * This method removes the UTC offset from the displayed timezone names.
     *
     * @return $this
     */
    public function omitOffset()
    {
        $this->showOffset = false;

        return $this;
    }

    /**
     * Generate HTML <option> tag
     *
     * @param string $timezone The timezone name
     * @param string|null $selected he value of the option to be pre-selected
     * @param string|null $continent The continent name
     */
    protected function makeOptionTag(string $timezone, ?string $selected, ?string $continent = null): string
    {
        $attrs = ($selected === $timezone) ? ' selected="selected"' : '';
        $display = $this->formatTimezone($timezone, $continent);

        return "<option value=\"{$timezone}\"{$attrs}>{$display}</option>";
    }

    /**
     * Checks if the general group should be included in the list
     */
    protected function hasGeneralGroup(): bool
    {
        return empty($this->selectedGroups) || in_array(self::GROUP_GENERAL, $this->selectedGroups);
    }

    /**
     * Loads the filtered list of continents based on the current group filter.
     *
     * @return array<string, int>
     */
    protected function loadContinents(): array
    {
        if (empty($this->selectedGroups)) {
            return self::CONTINENTS;
        }

        return Collection::make(self::CONTINENTS)
            ->only($this->selectedGroups)
            ->all();
    }

    /**
     * Formats a timezone name for display, optionally including the continent name
     * and offset.
     *
     * @param string $timezone The timezone name to format
     * @param string|null $continent The continent name to remove from
     *                               the timezone name (if applicable)
     * @param bool $htmlEncode Whether to HTML-encode the output
     */
    protected function formatTimezone(string $timezone, ?string $continent = null, bool $htmlEncode = true): string
    {
        $displayedTz = empty($continent) ? $timezone : substr($timezone, strlen($continent) + 1);
        $normalizedTz = str_replace(['St_', '/', '_'], ['St. ', ' / ', ' '], $displayedTz);

        if (!$this->showOffset) {
            return $normalizedTz;
        }

        $offset = $this->getOffset($timezone);
        $separator = $htmlEncode ? str_repeat(self::HTML_WHITESPACE, 3) : ' ';

        return "(UTC{$offset})".$separator.$normalizedTz;
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
     * Validate a list of groups
     *
     * @param array<string> $groups
     * @return void
     *
     * @throws \InvalidArgumentException When the groups are invalid
     */
    protected function validateGroups(array $groups)
    {
        $groups = array_map(fn($group) => ucfirst(strtolower($group)), $groups);
        $validGroups = array_merge(array_keys(self::CONTINENTS), [self::GROUP_GENERAL]);
        $invalidGroups = array_diff($groups, $validGroups);

        if (!empty($invalidGroups)) {
            throw new \InvalidArgumentException('Invalid groups: '.implode(', ', $invalidGroups));
        }
    }

    /**
     * Validate a timezone name and check if it is within the specified groups
     *
     * @param string $timezone The timezone name to validate
     * @return void
     *
     * @throws \InvalidArgumentException When the timezone is invalid
     * @throws \InvalidArgumentException When the timezone is not within the specified groups
     */
    protected function validateTimezone(string $timezone)
    {
        if (!in_array($timezone, \DateTimeZone::listIdentifiers())) {
            throw new \InvalidArgumentException('Invalid timezone: '.$timezone);
        }
        // Check if a filter is applied and if the timezone is within the filter
        if (!empty($this->selectedGroups)) {
            $timezoneContinent = explode('/', $timezone)[0];
            if (!in_array($timezoneContinent, $this->selectedGroups) && $timezone !== 'UTC') {
                asort($this->selectedGroups);
                throw new \InvalidArgumentException(sprintf(
                    'Timezone %s is not within the specified groups: %s',
                    $timezone,
                    implode(', ', $this->selectedGroups),
                ));
            }
        }
    }
}
