<?php

namespace Realodix\Timezone;

final class Timezone
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
    private array $activeGroups = [];

    /**
     * Whether to group timezones by continent
     */
    private bool $splitGroup = true;

    /**
     * Whether to display the timezone offset
     */
    private bool $showOffset = true;

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
            $options[] = $this->splitGroup ? '<optgroup label="'.$continent.'">' : '';
            foreach (timezone_identifiers_list($mask) as $timezoneId) {
                $options[] = $this->makeOptionTag($timezoneId, $selected);
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
        if ($this->hasGeneralGroup()) {
            if ($this->splitGroup) {
                $list[self::GROUP_GENERAL]['UTC'] = $this->formatTimezone('UTC');
            } else {
                $list['UTC'] = $this->formatTimezone('UTC');
            }
        }

        foreach ($this->loadContinents() as $continent => $mask) {
            foreach (timezone_identifiers_list($mask) as $timezoneId) {
                if ($this->splitGroup) {
                    $list[$continent][$timezoneId] = $this->formatTimezone($timezoneId);
                } else {
                    $list[$timezoneId] = $this->formatTimezone($timezoneId);
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
        $this->activeGroups = $this->processGroupNames($groups);

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
        $groups = $this->processGroupNames($groups);

        $this->activeGroups = $this->getGroups()
            ->diff($groups)
            ->all();

        return $this;
    }

    /**
     * Flattens the timezone list, removing the continental grouping.
     *
     * @return $this
     */
    public function flatten()
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
     */
    private function makeOptionTag(string $timezoneId, ?string $selected): string
    {
        $attrs = ($selected === $timezoneId) ? ' selected' : '';
        $display = $this->formatTimezone($timezoneId, true);

        return "<option value=\"{$timezoneId}\"{$attrs}>{$display}</option>";
    }

    /**
     * Checks if the general group should be included in the list
     */
    private function hasGeneralGroup(): bool
    {
        return empty($this->activeGroups) || in_array(self::GROUP_GENERAL, $this->activeGroups);
    }

    /**
     * Loads the filtered list of continents based on the current group filter.
     *
     * @return array<string, int>
     */
    private function loadContinents(): array
    {
        return collect(self::CONTINENTS)
            ->when(!empty($this->activeGroups), fn($c) => $c->only($this->activeGroups))
            ->all();
    }

    /**
     * Formats a timezone name for display, optionally including the continent name
     * and offset.
     *
     * @param string $timezoneId Timezone identifier (e.g. "America/New_York")
     * @param bool $htmlEncode Whether to HTML-encode the output
     */
    private function formatTimezone(string $timezoneId, bool $htmlEncode = false): string
    {
        $rawTzName = !$this->splitGroup || !str_contains($timezoneId, '/') ?
            $timezoneId : explode('/', $timezoneId, 2)[1];
        $fmtTzName = str_replace(['St_', '/', '_'], ['St. ', ' / ', ' '], $rawTzName);

        if (!$this->showOffset) {
            return $fmtTzName;
        }

        $offset = (new \DateTime('', new \DateTimeZone($timezoneId)))->format('P');
        $separator = $htmlEncode ? str_repeat(self::HTML_WHITESPACE, 3) : ' ';

        return "(UTC{$offset})".$separator.$fmtTzName;
    }

    /**
     * Get all defined group names (continents and the general).
     *
     * @return \Illuminate\Support\Collection<int, string>
     */
    private function getGroups()
    {
        return collect(self::CONTINENTS)
            ->keys()
            ->push(self::GROUP_GENERAL);
    }

    /**
     * Processes the given group names by uppercasing the first character
     * and ensuring they are valid.
     */
    private function processGroupNames(array $groups): array
    {
        $groups = array_map(fn($group) => ucfirst($group), $groups);
        $invalidGroups = array_diff($groups, $this->getGroups()->all());

        // When the groups are invalid
        if (!empty($invalidGroups)) {
            throw new \Realodix\Timezone\Exceptions\InvalidGroupException($invalidGroups);
        }

        return $groups;
    }

    /**
     * Validate a timezone name and check if it is within the specified groups
     *
     * @param string $timezoneId Timezone identifier (e.g. "America/New_York")
     * @return void
     */
    private function validateTimezone(string $timezoneId)
    {
        // When the timezone is invalid
        if (!in_array($timezoneId, timezone_identifiers_list())) {
            throw new \Realodix\Timezone\Exceptions\InvalidTimezoneException($timezoneId);
        }

        if (
            !empty($this->activeGroups) // when the groups are specified
            && $timezoneId !== 'UTC' // and the timezone is not UTC
            // and the timezone is not in the specified groups
            && !in_array(explode('/', $timezoneId, 2)[0], $this->activeGroups)
        ) {
            throw new \Realodix\Timezone\Exceptions\TimezoneOutOfScopeException(
                $timezoneId, $this->activeGroups,
            );
        }
    }
}
