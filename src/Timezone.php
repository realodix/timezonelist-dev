<?php

namespace Realodix\Timezone;

use Illuminate\Support\Str;

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
     * indicates all groups should be included.
     *
     * @var list<string>
     */
    protected array $activeGroups = [];

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

        $attributes = collect($attrs)
            ->map(fn($value, $key) => "{$key}=\"{$value}\"")
            ->implode(' ');

        $options = [];
        if ($this->hasGeneralGroup()) {
            $options[] = $this->splitGroup ? '<optgroup label="General">' : '';
            $options[] = $this->makeOptionTag('UTC', $selected);
            $options[] = $this->splitGroup ? '</optgroup>' : '';
        }

        foreach ($this->loadContinents() as $continent => $mask) {
            $timezoneIds = \DateTimeZone::listIdentifiers($mask);
            $options[] = $this->splitGroup ? '<optgroup label="'.$continent.'">' : '';
            foreach ($timezoneIds as $timezoneId) {
                $continent = $this->splitGroup ? $continent : null;
                $options[] = $this->makeOptionTag($timezoneId, $selected, $continent);
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
                $timezoneIds = \DateTimeZone::listIdentifiers($mask);
                foreach ($timezoneIds as $timezoneId) {
                    $list[$continent][$timezoneId] = $this->formatTimezone($timezoneId, $continent, false);
                }
            }
        } else {
            if ($this->hasGeneralGroup()) {
                $list['UTC'] = $this->formatTimezone('UTC', htmlEncode: false);
            }

            foreach ($this->loadContinents() as $continent => $mask) {
                $timezoneIds = \DateTimeZone::listIdentifiers($mask);
                foreach ($timezoneIds as $timezoneId) {
                    $list[$timezoneId] = $this->formatTimezone($timezoneId, htmlEncode: false);
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
        $groups = array_map(fn($group) => Str::title($group), $groups);
        $this->validateGroups($groups);
        $this->activeGroups = $groups;

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
        $groups = array_map(fn($group) => Str::title($group), $groups);
        $this->validateGroups($groups);

        $this->activeGroups = collect(self::CONTINENTS)
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
     * @param string $timezoneId Timezone identifier (e.g. "America/New_York")
     * @param string|null $selected The value of the option to be pre-selected
     * @param string|null $continent The continent name
     */
    protected function makeOptionTag(string $timezoneId, ?string $selected, ?string $continent = null): string
    {
        $attrs = ($selected === $timezoneId) ? ' selected' : '';
        $display = $this->formatTimezone($timezoneId, $continent);

        return "<option value=\"{$timezoneId}\"{$attrs}>{$display}</option>";
    }

    /**
     * Checks if the general group should be included in the list
     */
    protected function hasGeneralGroup(): bool
    {
        return empty($this->activeGroups) || in_array(self::GROUP_GENERAL, $this->activeGroups);
    }

    /**
     * Loads the filtered list of continents based on the current group filter.
     *
     * @return array<string, int>
     */
    protected function loadContinents(): array
    {
        if (empty($this->activeGroups)) {
            return self::CONTINENTS;
        }

        return collect(self::CONTINENTS)
            ->only($this->activeGroups)
            ->all();
    }

    /**
     * Formats a timezone name for display, optionally including the continent name
     * and offset.
     *
     * @param string $timezoneId Timezone identifier (e.g. "America/New_York")
     * @param string|null $continent The continent name to remove from
     *                               the timezone name (if applicable)
     * @param bool $htmlEncode Whether to HTML-encode the output
     */
    protected function formatTimezone(string $timezoneId, ?string $continent = null, bool $htmlEncode = true): string
    {
        $displayedTz = empty($continent) ? $timezoneId : substr($timezoneId, strlen($continent) + 1);
        $normalizedTz = str_replace(['St_', '/', '_'], ['St. ', ' / ', ' '], $displayedTz);

        if (!$this->showOffset) {
            return $normalizedTz;
        }

        $offset = (new \DateTime('', new \DateTimeZone($timezoneId)))->format('P');
        $separator = $htmlEncode ? str_repeat(self::HTML_WHITESPACE, 3) : ' ';

        return "(UTC{$offset})".$separator.$normalizedTz;
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
        $validGroups = array_merge(array_keys(self::CONTINENTS), [self::GROUP_GENERAL]);
        $invalidGroups = array_diff($groups, $validGroups);

        if (!empty($invalidGroups)) {
            throw new \InvalidArgumentException('Invalid groups: '.implode(', ', $invalidGroups));
        }
    }

    /**
     * Validate a timezone name and check if it is within the specified groups
     *
     * @param string $timezoneId Timezone identifier (e.g. "America/New_York")
     * @return void
     *
     * @throws \DateInvalidTimeZoneException When the timezone is invalid
     * @throws \InvalidArgumentException When the timezone is not within the specified groups
     */
    protected function validateTimezone(string $timezoneId)
    {
        if (!in_array($timezoneId, \DateTimeZone::listIdentifiers())) {
            throw new \DateInvalidTimeZoneException('Invalid timezone: '.$timezoneId);
        }
        // Check if a filter is applied and if the timezone is within the filter
        if (!empty($this->activeGroups)) {
            $continent = explode('/', $timezoneId)[0];
            if (!in_array($continent, $this->activeGroups) && $timezoneId !== 'UTC') {
                asort($this->activeGroups);
                throw new \InvalidArgumentException(sprintf(
                    'Timezone %s is not within the specified groups: %s',
                    $timezoneId,
                    implode(', ', $this->activeGroups),
                ));
            }
        }
    }
}
