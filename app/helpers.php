<?php
use App\Helpers\DateHelper;


if (!function_exists('format_number')) {
    /**
     * Format number with thousand separators and remove .00 if present
     *
     * @param float|int|string $number
     * @return string
     */
    function format_number($number)
    {
        // Handle null or empty values
        if ($number === null || $number === '') {
            return '0';
        }

        // Format with 2 decimal places first
        $formatted = number_format($number, 2, '.', ',');

        // Remove .00 if it exists at the end
        if (substr($formatted, -3) === '.00') {
            return substr($formatted, 0, -3);
        }

        return $formatted;
    }

    if (!function_exists('arabic_date')) {
        function arabic_date($date)
        {
            return DateHelper::formatArabic($date);
        }
    }

    if (!function_exists('arabic_date_time')) {
        function arabic_date_time($date)
        {
            return DateHelper::formatArabicWithTime($date);
        }
    }

    if (!function_exists('relative_time')) {
        function relative_time($date)
        {
            return DateHelper::shortRelative($date);
        }
    }

}