<?php

use CodeIgniter\I18n\Time;
use Config\App;
use CodeIgniter\Database\Config;

// Define constants
if (!defined('CC_SITE_NAME')) {
    define('CC_SITE_NAME', (setting('App.siteName') == '') ? 'Carbone Clinic' : setting('App.siteName'));
}
if (!defined('CC_TIME_ZONE')) {
    define('CC_TIME_ZONE', (setting('App.appTimezone') == '') ? 'UTC' : setting('App.appTimezone'));
}
if (!defined('CC_DATE_FORMAT')) {
    define('CC_DATE_FORMAT', (setting('App.dateFormat') == '') ? 'Y-m-d' : setting('App.dateFormat'));
}
if (!defined('CC_TIME_FORMAT')) {
    define('CC_TIME_FORMAT', (setting('App.timeFormat') == '') ? 'H:i:s' : setting('App.timeFormat'));
}
if (!defined('CC_WEEK_START_DAY')) {
    define('CC_WEEK_START_DAY', (setting('App.weekStartDay') == '') ? '1' : setting('App.weekStartDay'));
}

if (!defined('CC_WEEK_START_DAY')) {
    define('CC_WEEK_START_DAY', (setting('App.weekStartDay') == '') ? '1' : setting('App.weekStartDay'));
}

if (!defined('SESSION_PROCESSING_RESOLUTION_DAYS')) {
    define('SESSION_PROCESSING_RESOLUTION_DAYS', setting('App.sessionProcessingResolutionDays') ?: 1);
}


// Initialize timezones
if (!function_exists('initialize_timezones')) {
    function initialize_timezones()
    {
        /*static $initialized = false;

        if ($initialized) {
            return;
        }

        $initialized = true;*/
        $appTimezone = CC_TIME_ZONE;

        // Set application timezone
        date_default_timezone_set($appTimezone);
        $appConfig = config('App');
        $appConfig->appTimezone = $appTimezone;

        // Set database timezone
        //$db = Config::connect();
        //$db->query("SET @@session.time_zone = '{$appTimezone}'");
        //log_message("info", "Timezone Initialized: '{$appTimezone}'");
    }
}

// Utility functions
if (!function_exists('has_error')) {
    function has_error(string $field): bool
    {
        if (!session()->has('errors')) {
            return false;
        }

        return isset(session('errors')[$field]);
    }
}

if (!function_exists('error')) {
    function error(string $field)
    {
        return session('errors')[$field] ?? '';
    }
}

if (!function_exists('app_date')) {
    function app_date($date, bool $includeTime = false, bool $includeTimezone = false): string
    {
        $format = $includeTime
            ? [
                CC_DATE_FORMAT,
                CC_TIME_FORMAT,
                $includeTimezone ? 'T' : '',
            ]
            : [
                CC_DATE_FORMAT,
                $includeTimezone ? 'T' : '',
            ];

        $format = trim(implode(' ', $format));

        if (is_string($date)) {
            $date = Time::parse($date);
        }

        $date->setTimezone(CC_TIME_ZONE);

        return $date->format($format);
    }
}

if (!function_exists('currentDate')) {
    function currentDate($format = 'Y-m-d')
    {
        $datetime = new Time("now", CC_TIME_ZONE);
        return $datetime->format($format);
    }
}

if (!function_exists('stringToDate')) {
    function stringToDate($date, $format = 'Y-m-d')
    {
        $datetime = new Time($date, CC_TIME_ZONE);
        return $datetime->format($format);
    }
}

if (!function_exists('normalize_reinforcer_input')) {
    /**
     * Normalize reinforcer text for consistent storage and duplicate checks:
     * - trims edges
     * - replaces tabs/newlines with spaces
     * - collapses repeated whitespace
     * - formats to existing app style (ucfirst + strtolower)
     */
    function normalize_reinforcer_input(?string $value): string
    {
        $text = (string) ($value ?? '');
        if ($text === '') {
            return '';
        }

        $text = str_replace(["\r", "\n", "\t"], ' ', $text);
        $text = preg_replace('/\s+/u', ' ', trim($text));
        if ($text === null) {
            $text = trim((string) ($value ?? ''));
        }

        if ($text === '') {
            return '';
        }

        return ucfirst(strtolower($text));
    }
}

if (!function_exists('to_sql_date')) {
    /**
     * Normalize any incoming date string to SQL format (Y-m-d)
     * Returns null for empty or invalid values.
     */
    function to_sql_date($date): ?string
    {
        if (empty($date)) {
            return null;
        }

        try {
            // If already Y-m-d or valid ISO, return as-is
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                return $date;
            }

            // Attempt using Time parser (handles various formats)
            $time = new \CodeIgniter\I18n\Time($date, CC_TIME_ZONE);
            return $time->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}


if (!function_exists('week_start_end_dates')) {
    function week_start_end_dates($date, $format = 'Y-m-d')
    {
        $weekStartDay = CC_WEEK_START_DAY;

        $datetime = new Time($date, CC_TIME_ZONE);
        $dayOfWeek = (int)$datetime->format('w');
        $weekStartOffset = ($dayOfWeek + 7 - $weekStartDay) % 7;
        $weekEndOffset = 6 - $weekStartOffset;
        $weekStart = clone $datetime;
        $dates['week_start'] = $weekStart->modify("-{$weekStartOffset} days")->format($format);
        $weekEnd = clone $datetime;
        $dates['week_end'] = $weekEnd->modify("+{$weekEndOffset} days")->format($format);
        return $dates;
    }
}
if (!function_exists('get_week_end_date')) {
    /**
     * Get the week end date for a given date using CC_WEEK_START_DAY (0=Sun ... 6=Sat).
     * Mainly used for reporting/grouping weekly data.
     *
     * @param string $date  The date to calculate from (Y-m-d or parseable string)
     * @param string $format Return format (default: Y-m-d)
     * @return string
     */
    function get_week_end_date(string $date, string $format = 'Y-m-d'): string
    {
        $weekStartDay = (int) CC_WEEK_START_DAY;

        $datetime = new \CodeIgniter\I18n\Time($date, CC_TIME_ZONE);
        $dayOfWeek = (int)$datetime->format('w'); // 0 = Sunday, 6 = Saturday

        $daysSinceWeekStart = ($dayOfWeek - $weekStartDay + 7) % 7;
        $weekStart = $datetime->subDays($daysSinceWeekStart);
        $weekEnd = $weekStart->addDays(6);

        return $weekEnd->format($format);
    }
}
if (!function_exists('getDaysDifference')) {
    function getDaysDifference($start_date, $end_date = null)
    {
        $timezone = CC_TIME_ZONE;

        $start_datetime = new Time($start_date, $timezone);

        if ($end_date === null) {
            $end_datetime = new Time("now", $timezone);
        } else {
            $end_datetime = new Time($end_date, $timezone);
        }

        $interval = $end_datetime->diff($start_datetime);
        $days_difference = $interval->days;

        return $days_difference;
    }
}

if (!function_exists('generate_random_string')) {
    function generate_random_string($length)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $characters_length = strlen($characters);
        $random_string = '';
        for ($i = 0; $i < $length; $i++) {
            $random_string .= $characters[random_int(0, $characters_length - 1)];
        }
        return $random_string;
    }
}



if (!function_exists('encodeValue')) {
    function encodeValue($value)
    {
        // Convert to string and Base64 encode
        $base64 = base64_encode((string)$value);
        // Make the Base64 URL-safe
        $urlSafeBase64 = str_replace(['+', '/', '='], ['-', '_', ''], $base64);
        // Ensure it meets permitted URI characters
        return urlencode($urlSafeBase64);
    }
}

if (!function_exists('decodeValue')) {
    function decodeValue($value)
    {
        // Reverse URL encoding
        $urlDecoded = urldecode($value);
        // Replace URL-safe Base64 characters with standard Base64 characters
        $base64 = str_replace(['-', '_'], ['+', '/'], $urlDecoded);
        // Base64 decode the string
        $decoded = base64_decode($base64);

        if ($decoded === false) {
            throw new InvalidArgumentException("Invalid encoding.");
        }

        return $decoded;
    }
}


if (!function_exists('get_phase_name')) {
    function get_phase_name($id)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('target_phases');
        $phase = $builder->select('name')->where('id', $id)->get()->getRow();

        return $phase ? $phase->name : '?';
    }
}

if (!function_exists('get_time_difference')) {
    /**
     * Calculate the difference between two times and return in the specified format.
     *
     * @param string $start_time The start time in 'H:i:s' format.
     * @param string|null $end_time The end time in 'H:i:s' format. If null, it assumes the current time.
     * @param string $format The format type. Accepts 'human' for human-readable format or 'H:i:s' for exact format.
     * @return string The time difference in the specified format.
     */
    function get_time_difference($start_time, $end_time = null, $format = 'human')
    {
        // If no end time is provided, use the current time
        $timezone = CC_TIME_ZONE;

        $startTime = new \DateTime($start_time, new \DateTimeZone($timezone));
        if ($end_time === null) {
            $endTime = new \DateTime("now", new \DateTimeZone($timezone));
        } else {
            $endTime = new \DateTime($end_time, new \DateTimeZone($timezone));
        }

        // Calculate the time difference
        $interval = $startTime->diff($endTime);

        // If format is 'H:i:s', return in hours, minutes, and seconds
        if ($format === 'H:i:s') {
            return $interval->format('%H:%I:%S');
        }

        // Otherwise, return the human-readable format
        $formattedDifference = [];
        if ($interval->h > 0) {
            $formattedDifference[] = $interval->h . ' hours';
        }
        if ($interval->i > 0) {
            $formattedDifference[] = $interval->i . ' minutes';
        }
        if ($interval->s > 0) {
            $formattedDifference[] = $interval->s . ' seconds';
        }

        // Return the formatted string or '0 seconds' if there is no difference
        return !empty($formattedDifference) ? implode(' ', $formattedDifference) : '0 seconds';
    }
    if (!function_exists('convertDecimalToTime')) {
        function convertDecimalToTime($decimalHours)
        {
            // Total seconds
            $totalSeconds = $decimalHours * 3600;

            // Calculate hours, minutes, and seconds
            $hours = floor($totalSeconds / 3600);
            $minutes = floor(($totalSeconds % 3600) / 60);
            $seconds = $totalSeconds % 60;

            // Format as H:i:s
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        }
    }
}
