<?php

/* ────────────────────────────────────────────────────────────────────────── */
/*                                  TimeUtil                                  */
/* ────────────────────────────────────────────────────────────────────────── */

class Times {
    /* ───────────────────────────────────────────────────────────────────── */
    /*                            Get Current Time                           */
    /* ───────────────────────────────────────────────────────────────────── */
    public function getCurrentTime(string $format, string $timezone) : string {
        $dt = new DateTime('now');
        $tz = new DateTimeZone($timezone);
        $dt->setTimeZone($tz);
        $return = $dt->format($format);
    
        return $return;
    }

    /* ───────────────────────────────────────────────────────────────────── */
    /*                             Relative Time                             */
    /* ───────────────────────────────────────────────────────────────────── */
    public function relativeTime($time, $format = null) {
        $then     = new DateTime('now');
        $now      = new DateTime($time);
        $diff     = $now->diff($then);

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

?>