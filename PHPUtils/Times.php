<?php

declare(strict_types=1);

namespace PHPUtils;

/* ────────────────────────────────────────────────────────────────────────── */
/*                                  TimeUtil                                  */
/* ────────────────────────────────────────────────────────────────────────── */

/**
 * Class Times
 */
class Times {
    /* ───────────────────────────────────────────────────────────────────── */
    /*                            Get Current Time                           */
    /* ───────────────────────────────────────────────────────────────────── */    
    /**
     * getCurrentTime
     *
     * @param  string $format The format to return the time in
     * @param  string $timezone The timezone to return the time in
     * @return string The current time
     */
    public function getCurrentTime(string $format, string $timezone): string {
        $dt = new \DateTime('now');
        $tz = new \DateTimeZone($timezone);
        $dt->setTimeZone($tz);
        $return = $dt->format($format);
    
        return $return;
    }

    /* ───────────────────────────────────────────────────────────────────── */
    /*                             Relative Time                             */
    /* ───────────────────────────────────────────────────────────────────── */    
    /**
     * relativeTime
     *
     * @param  mixed $time The time to compare
     * @param  mixed $format The format to return the time in. Defaults to null
     * @return string The relative time
     */
    public function relativeTime($time, $format = null) {
        $currentTime = new \DateTime('now');
        $inputTime   = new \DateTime($time);
        $diff        = $currentTime->diff($inputTime);

        # Translate the format
        $formats = [
            'days' => '%a',
            'hours' => '%h',
            'minutes' => '%i',
        ];

        # Return format is specified
        if (!empty($format) && !empty($formats[$format])) {
            $format = $formats[$format];
            return $diff->format($format);
        }

        // $formattedTimeLeft = $diff->format($format);
        foreach ($formats as $unit => $f) {
            $amt = $diff->format($f);
            if ($amt > 1) {
                return "$amt $unit";
            }
            if ($amt == 1) {
                return "$amt ".substr($unit, 0, -1);
            }
        }
        # Automatically format the time left
        // # Days
        // $days = $diff->format($formats['days']);
        // if ($days > 0) {
        //     if ($days > 1) {
        //         $s = 's';
        //     }
        //     return $days.' day'.$s;
        // }

        // # Hours
        // $hours = $diff->format($formats['hours']);
        // if ($hours > 0) {
        //     return $hours.' hours';
        // }

        // # Minutes
        // $minutes = $diff->format($formats['minutes']);
        // if ($minutes > 0) {
        //     return $minutes.' minutes';
        // }

        return 'now';
    }
}