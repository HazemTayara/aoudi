<?php

namespace App\Helpers;

use Carbon\Carbon;

class DateHelper
{
    /**
     * Format date in Arabic human-readable format
     *
     * @param Carbon|string|null $date
     * @return string
     */
    public static function formatArabic($date)
    {
        if (!$date) {
            return '—';
        }

        if (!$date instanceof Carbon) {
            $date = Carbon::parse($date);
        }

        if ($date->isToday()) {
            return 'اليوم';
        }

        if ($date->isYesterday()) {
            return 'الأمس';
        }

        if ($date->diffInDays() < 7) {
            $days = $date->diffInDays();
            return "منذ {$days} أيام";
        }

        if ($date->isCurrentMonth()) {
            return $date->format('d M');
        }

        return $date->format('Y-m-d');
    }

    /**
     * Format date with time
     *
     * @param Carbon|string|null $date
     * @return string
     */
    public static function formatArabicWithTime($date)
    {
        if (!$date) {
            return '—';
        }

        if (!$date instanceof Carbon) {
            $date = Carbon::parse($date);
        }

        if ($date->isToday()) {
            return 'اليوم ' . $date->format('H:i');
        }

        if ($date->isYesterday()) {
            return 'الأمس ' . $date->format('H:i');
        }

        if ($date->diffInDays() < 7) {
            $days = $date->diffInDays();
            return "منذ {$days} أيام";
        }

        if ($date->isCurrentMonth()) {
            return $date->format('d M H:i');
        }

        return $date->format('Y-m-d H:i');
    }

    /**
     * Format date as short relative time
     *
     * @param Carbon|string|null $date
     * @return string
     */
    public static function shortRelative($date)
    {
        if (!$date) {
            return '—';
        }

        if (!$date instanceof Carbon) {
            $date = Carbon::parse($date);
        }

        $now = Carbon::now();
        $diffInMinutes = $date->diffInMinutes($now);
        $diffInHours = $date->diffInHours($now);
        $diffInDays = $date->diffInDays($now);

        if ($diffInMinutes < 1) {
            return 'الآن';
        }

        if ($diffInMinutes < 60) {
            return "منذ {$diffInMinutes} دقيقة";
        }

        if ($diffInHours < 24) {
            return "منذ {$diffInHours} ساعة";
        }

        if ($date->isYesterday()) {
            return 'الأمس';
        }

        if ($diffInDays < 7) {
            return "منذ {$diffInDays} أيام";
        }

        if ($date->isCurrentMonth()) {
            return $date->format('d M');
        }

        if ($date->isCurrentYear()) {
            return $date->format('d M');
        }

        return $date->format('Y-m-d');
    }
}