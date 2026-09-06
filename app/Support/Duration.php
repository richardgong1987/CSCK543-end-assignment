<?php

namespace App\Support;

class Duration
{
    /**
     * "45 mins", "1 hr 20 mins", "8 hrs" -- long marinating and chilling times read
     * badly when left in minutes.
     */
    public static function format(int $minutes): string
    {
        $hours = intdiv($minutes, 60);
        $remainder = $minutes % 60;

        if ($hours === 0) {
            return $remainder === 1 ? '1 min' : "{$remainder} mins";
        }

        $hoursText = $hours === 1 ? '1 hr' : "{$hours} hrs";

        return $remainder === 0 ? $hoursText : "{$hoursText} {$remainder} mins";
    }
}
