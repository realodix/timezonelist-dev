# Realodix\Timezone

![PHPVersion](https://img.shields.io/badge/PHP-8.1-777BB4.svg?style=flat-square)
[![GitHub license](https://img.shields.io/github/license/realodix/timezone?style=flat-square)](https://github.com/realodix/timezone/blob/main/LICENSE)

A PHP library that provides an easy way to generate HTML select boxes for timezones. It offers features to filter timezones by continent, group them, display offsets, and customize the output.

## Features
- Generate a list of all available timezones.
- Optionally group timezones by continent.
- Include or exclude specific continents/groups.
- Display timezone offsets (e.g., UTC+05:30).
- Get an array of timezones for use in your applications.


## Installation

You can install this package via Composer:

```bash
composer require realodix/timezone
```

### Project status & release process
While this library is still under development, it is well tested and should be stable enough to use in production environments.

The current releases are numbered `0.x.y`. When a non-breaking change is introduced (adding new methods, optimizing existing code, etc.), `y` is incremented.

**When a breaking change is introduced, a new `0.x` version cycle is always started.** It is therefore safe to lock your project to a given release cycle, such as `0.1.*`. If you need to upgrade to a newer release cycle, check the [release history](/releases) for a list of changes introduced by each further `0.x.0` version.

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
use Realodix\Timezone\Timezone;

$tz = new Timezone;
$attributes = ['class' => 'form-control', 'id' => 'timezone-select'];

$tz->toSelectBox('timezone', 'America/New_York', $attributes);
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
        <option value="America/New_York" selected>(UTC-05:00) New York</option>
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
use Realodix\Timezone\Timezone;

$tz = new Timezone;

$tz->toArray();
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
Realodix\Timezone provides several methods to customize the timezone list:

- `onlyGroups(array $groups)`: Includes only the specified continent/group names. For example, `$tz->onlyGroups(['America', 'Europe'])` will only return timezones within the America and Europe continents.
- `excludeGroups(array $groups)`: Excludes the specified continent/group names. For example, `$tz->excludeGroups(['Arctic'])` will exclude all timezones from Africa.
- `flatten()`: Flattens the timezone list, removing the continental grouping.
- `omitOffset()`: Removes the UTC offset from the displayed timezone names.

#### Timezones groups
- General
- Africa
- America
- Antarctica
- Arctic
- Asia
- Atlantic
- Australia
- Europe
- Indian
- Pacific

## License
This package is licensed using the [MIT License](/LICENSE).
