<?php

namespace Realodix\Timezonelist;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Realodix\Timezonelist\Exceptions\OutOfScopeTimezoneException;

class Timezonelist
{
    const HTML_WHITESPACE = '&#160;';

    /**
     * Array of continents and their corresponding DateTimeZone constants.
     */
    protected array $continents = [
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
     * Array of continent/group names to include in the timezone list. An empty
     * array indicates all groups.
     */
    protected array $groupsFilter = [];

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
        $attributes = collect($attrs)
            ->map(fn ($value, $key) => "{$key}=\"{$value}\"")
            ->implode(' ');

        return $this->makeSelectTag($name, $selected, $attributes, $this->splitGroup);
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
        return tap(Collection::make(), function (Collection $list) {
            if ($this->includeGeneral()) {
                $timezone = 'UTC';
                $list->put(
                    $this->splitGroup ? 'General' : $timezone,
                    $this->splitGroup ? [$timezone => $this->formatTimezone($timezone)] : $this->formatTimezone($timezone)
                );
            }

            Collection::make($this->loadContinents())->each(function ($mask, $continent) use ($list) {
                $timezones = \DateTimeZone::listIdentifiers($mask);

                foreach ($timezones as $timezone) {
                    if ($this->splitGroup) {
                        $list->put($continent, Arr::get($list->toArray(), $continent, []) + [$timezone => $this->formatTimezone($timezone, $continent)]);
                    } else {
                         $list->put($timezone, $this->formatTimezone($timezone));
                    }
                }
            });
        })->toArray();
    }

    /**
     * Sets the filter to include only the specified continent/group names
     *
     * @param array<string> $groups The continent/group names to include.
     * @return $this
     */
    public function onlyGroups(array $groups = []): self
    {
        $this->groupsFilter = collect($groups)
            ->map(fn ($group) => Str::ucfirst(Str::lower($group)))
            ->all();

        return $this;
    }

    /**
     * Sets the filter to exclude the specified continent/group names
     *
     * @param array<string> $groups The continent/group names to exclude
     * @return $this
     */
    public function excludeGroups(array $groups = []): self
    {
        if (empty($groups)) {
            $this->groupsFilter = [];

            return $this;
        }

        $groups = collect($groups)
            ->map(fn ($group) => Str::ucfirst(Str::lower($group)))
            ->all();

        $this->groupsFilter = collect($this->continents)
            ->keys()
            ->diff($groups)
            ->values()
            ->all();

        if (!in_array('General', $groups)) {
            $this->groupsFilter[] = 'General';
        }

        return $this;
    }

    /**
     * Sets whether to split the timezone list into groups (continents)
     *
     * @param bool $value Whether to split into groups
     * @return $this
     */
    public function splitGroup(bool $value = true): self
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
    public function showOffset(bool $value = true): self
    {
        $this->showOffset = $value;

        return $this;
    }

    /**
     * Generates the HTML for the select element, including options and optional optgroups.
     *
     * @param string $name The name attribute of the select tag
     * @param string|null $selected The value of the option to be pre-selected
     * @param string|null $attrs Additional HTML attributes
     * @param bool $withGroup Whether to use optgroup tags to group options
     */
    protected function makeSelectTag(string $name, ?string $selected, ?string $attrs, bool $withGroup): string
    {
        if ($selected) {
            $this->validateTimezone($selected);
        }

        $output = "<select name=\"{$name}\" {$attrs}>";

        $options = Collection::make();

        if ($this->includeGeneral()) {
            if ($withGroup) {
                $options->push('<optgroup label="General">');
            }

            $timezone = 'UTC';
            $options->push($this->makeOptionTag(
                $this->formatTimezone($timezone),
                $timezone,
                ($selected === $timezone),
            ));

            if ($withGroup) {
                $options->push('</optgroup>');
            }
        }

        collect($this->loadContinents())->each(function ($mask, $continent) use ($options, $withGroup, $selected) {
            $timezones = \DateTimeZone::listIdentifiers($mask);

            if ($withGroup) {
                $options->push("<optgroup label=\"{$continent}\">");
            }

            collect($timezones)->each(function ($timezone) use ($options, $continent, $withGroup, $selected) {
                $cutOffContinent = $withGroup ? $continent : null;

                $options->push($this->makeOptionTag(
                    $this->formatTimezone($timezone, $cutOffContinent),
                    $timezone,
                    ($selected === $timezone),
                ));
            });

            if ($withGroup) {
                $options->push('</optgroup>');
            }
        });

        $output .= $options->implode('');
        $output .= '</select>';

        return $output;
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
        return empty($this->groupsFilter) || in_array('General', $this->groupsFilter);
    }

    /**
     * Loads the filtered list of continents based on the current group filter.
     */
    protected function loadContinents(): array
    {
        return collect($this->continents)
            ->when(!empty($this->groupsFilter), function (Collection $collection) {
                return $collection->filter(fn ($value, $key) => in_array($key, $this->groupsFilter));
            })
            ->all();
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

        if (! $this->showOffset) {
            return $normalizedTz;
        }

        $offset = $this->getOffset($timezone);
        $separator = str_repeat(self::HTML_WHITESPACE, 3);

        return "(UTC{$offset}){$separator}{$normalizedTz}";
    }

    /**
     * Normalizes a timezone name
     *
     * @param string $timezone The timezone name to normalize
     */
    protected function normalizeTimezone(string $timezone): string
    {
        return Str::replace(['St_', '/', '_'], ['St. ', ' / ', ' '], $timezone);
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
     * @param string $timezone The timezone name to validate
     * @return void
     *
     * @throws \InvalidArgumentException When the timezone is invalid
     * @throws OutOfScopeTimezoneException When the timezone is not within the specified groups
     */
    protected function validateTimezone(string $timezone): void
    {
        if (!in_array($timezone, \DateTimeZone::listIdentifiers())) {
            throw new \InvalidArgumentException('Invalid timezone: '.$timezone);
        }
        // Check if a filter is applied and if the timezone is within the filter
        if (!empty($this->groupsFilter)) {
            $timezoneContinent = explode('/', $timezone)[0];
            if (!in_array($timezoneContinent, $this->groupsFilter) && $timezone !== 'UTC') {
                throw new OutOfScopeTimezoneException($timezone, $this->groupsFilter);
            }
        }
    }
}
