# Timezonelist

![PHPVersion](https://img.shields.io/badge/PHP-8.1-777BB4.svg?style=flat-square)
[![GitHub license](https://img.shields.io/github/license/realodix/timezonelist?style=flat-square)](https://github.com/realodix/timezonelist/blob/main/LICENSE)

Timezonelist is a PHP library that provides an easy way to generate HTML select boxes for timezones. It offers features to filter timezones by continent, group them, display offsets, and customize the output.

## Features
- Generate a list of all available timezones.
- Optionally group timezones by continent.
- Include or exclude specific continents/groups.
- Display timezone offsets (e.g., UTC+05:30).
- Get an array of timezones for use in your applications.


## Installation

You can install this package via Composer:

```bash
composer require realodix/timezonelist
```

## Usage

### Create HTML select box

```php
/**
 * Creates an HTML select box of timezones
 *
 * @param string $name The name attribute of the select tag
 * @param string|null $selected The value of the option to be pre-selected
 * @param array|null $attrs Additional HTML attributes
 */
public function toSelectBox(string $name, ?string $selected = null, ?array $attrs = null): string
```

```php
use Realodix\Timezonelist\Timezonelist;

$tzList = new Timezonelist();
$attributes = ['class' => 'form-control', 'id' => 'timezone-select'];

$tzList->toSelectBox('timezone', 'America/New_York', $attributes);
```

Output:
```html
<select name="timezone" class="form-control" id="timezone-select">
    <optgroup label="General">
        <option value="UTC">(UTC+00:00) UTC</option>
    </optgroup>
    ...
    <optgroup label="America">
        ...
        <option value="America/Nassau">(UTC-05:00) Nassau</option>
        <option value="America/New_York" selected="selected">(UTC-05:00) New York</option>
        <option value="America/Nome">(UTC-09:00) Nome</option>
        ...
    </optgroup>
    ...
    <optgroup label="Asia">
        <option value="Asia/Aden">(UTC+03:00) Aden</option>
        <option value="Asia/Almaty">(UTC+05:00) Almaty</option>
        ...
    </optgroup>
    ...
</select>
```


### Create timezone list array

```php
/**
 * Generates an array of timezones, with optional grouping by continent
 *
 * If $this->splitGroup is true, the array will be a multidimensional array with
 * the first key being the continent. Otherwise, the array will be a flat array
 * of timezones.
 */
public function toArray(): array
```

```php
use Realodix\Timezonelist\Timezonelist;

$tzList = new Timezonelist();

$tzList->toArray();
```

Output:
```php
[
    'UTC' => '(UTC+00:00) UTC',
    'America' => [
        'Nassau' => '(UTC-05:00) Nassau',
        'New_York' => '(UTC-05:00) New York',
        'Nome' => '(UTC-09:00) Nome'
        // ...
    ],
    // ...
]
```

### Filtering and Grouping Timezones
Timezonelist provides several methods to customize the timezone list:

- `onlyGroups(array $groups)`: Includes only the specified continent/group names. For example, `$tzList->onlyGroups(['America', 'Europe'])` will only return timezones within the America and Europe continents.
- `excludeGroups(array $groups)`: Excludes the specified continent/group names. For example, `$tzList->excludeGroups(['Africa'])` will exclude all timezones from Africa.
- `splitGroup(bool $value)`: Determines whether the timezone list is grouped by continent.
- `showOffset(bool $value)`: Determines whether the timezone offset (e.g., UTC-05:00) is displayed.

## License
This package is licensed using the [MIT License](/LICENSE).
