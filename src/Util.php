<?php

namespace Realodix\Timezone;

final class Util
{
    /**
     * Convert a timestamp from one timezone to another
     *
     * @param string $timestamp The timestamp to convert
     * @param string $from Current timezone
     * @param string $to Target timezone
     * @param string $format Output format
     */
    public static function convertTimezone(string $timestamp, string $from, string $to, string $format = 'Y-m-d H:i:s'): string
    {
        $date = new \DateTime($timestamp, new \DateTimeZone($from));

        $date->setTimezone(new \DateTimeZone($to));

        return $date->format($format);
    }

    /**
     * Extract the continent from a timezone identifier
     *
     * @param string $timezoneId The timezone identifier (e.g. "America/New_York")
     * @return string The extracted continent (e.g. "America")
     */
    public static function extractContinent(string $timezoneId): string
    {
        return explode('/', $timezoneId)[0];
    }

    /**
     * Extract the location from a timezone identifier
     *
     * @param string $timezoneId The timezone identifier (e.g. "America/New_York")
     * @return string The extracted location (e.g. "New_York")
     */
    public static function extractLocation(string $timezoneId): string
    {
        if (!str_contains($timezoneId, '/')) {
            return $timezoneId;
        }

        return explode('/', $timezoneId, 2)[1];
    }

    /**
     * Shorten a timezone offset to its short form
     *
     * Examples:
     *  - "+00:00" will be returned as "+0"
     *  - "-01:00" will be returned as "-1"
     *  - "+01:30" will be returned as "+1:30"
     *
     * @param string $offset The timezone offset to shorten
     */
    public static function shortOffset(string $offset): string
    {
        if (preg_match('/^([+-])(\d{2}):(\d{2})$/', $offset, $matches)) {
            $sign = $matches[1];
            $hours = (int) $matches[2]; // Convert to integer to avoid leading zeros
            $minutes = $matches[3];

            if ($minutes === '00') {
                return $sign.$hours;
            }

            return $sign.$hours.':'.$minutes;
        }

        return $offset;
    }

    /**
     * Checks if a timezone string is valid
     *
     * @param string $timezone The timezone to check
     */
    public static function isTimezone(string $timezone): bool
    {
        if (!in_array($timezone, \DateTimeZone::listIdentifiers())) {
            return false;
        }

        return true;
    }
}
