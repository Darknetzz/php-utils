<?php

declare(strict_types=1);

namespace PHPUtils;

/**
 * The Random class provides various randomization functions.
 */
class Random extends Base {
    
    /**
     * Picks a random element from an array.
     *
     * @param  array $array The array to pick a random element from.
     * @return mixed       The randomly picked element.
     */
    public function array_pick_random(array $array) {
        $rand = mt_rand(0, count($array) - 1);
        $pick = $array[$rand];
        return $pick;
    }

    
    /**
     * Generates a random string.
     *
     * @param  int    $len       The length of the generated string. Default is 24.
     * @param  array  $charset   The character set to use for generating the string. Default is an empty array.
     * @param  array  $required  The types of characters that must be included in the generated string. Default is an empty array.
     * @return string            The generated random string.
     */
    public function genStr(int $len = 24, mixed $charset = [], $required = []) {
        
        $str['lowercase'] = range('a', 'z');
        $str['uppercase'] = range('A', 'Z');
        $str['digits']    = range(0, 9);
        $str['symbols']   = str_split("!.:,;-");
        $str['all']       = array_merge($str['lowercase'], $str['uppercase'], $str['digits'], $str['symbols']);
        $str['default']   = array_merge($str['lowercase'], $str['uppercase'], $str['digits']);

        if (is_string($charset)) {
            $charset = str_split('', $charset);
        }
        
        if (empty($charset)) {
            foreach ($str as $this_str) {
                $charset = $str['default'];
            }
        }
        
        $count = count($charset);
        $returnValue = "";
        
        for ($i = 0; $i <= $len - 1; $i++) {
            $roll = mt_rand(0, $count - 1);
            $returnValue .= $charset[$roll];
        }

        $requiredTypes = [
            'lowercase',
            'uppercase',
            'digits',
            'symbols',
        ];

        foreach ($requiredTypes as $type) {

            if (empty($str[$type])) {
                throw new \InvalidArgumentException(__METHOD__ . ": Invalid type: $type");
            }

            if (in_array($type, $required) && strpos($returnValue, implode('', $str[$type])) !== False) {
                $returnValue .= $this->array_pick_random($str[$type]);
            }
        }

        $returnValue = str_shuffle($returnValue); 

        return $returnValue;
    }

        
    /**
     * Rolls a random number between the specified range.
     *
     * @param  int    $from  The minimum value of the range. Default is 1.
     * @param  int    $to    The maximum value of the range. Default is 100.
     * @return int           The randomly rolled number.
     */
    public function roll($from = 1, $to = 100) {
        return mt_rand($from, $to);
    }
    
        
    /**
     * Determines if a random event occurs based on a given chance.
     *
     * @param  int     $chance  The chance of the event occurring, in percentage. Default is 50.
     * @return bool             True if the event occurs, false otherwise.
     */
    public function percentage(int $chance = 50) {
        return (mt_rand(0, 100) <= $chance);
    }

    /**
     * Uniform sample on the open interval (0, 1).
     * Never returns 0, so it is safe to pass to log().
     */
    public function uniformOpen(): float
    {
        return (mt_rand() + 1) / (mt_getrandmax() + 1);
    }

    /**
     * Sample from Normal(mean, stddev) via Box–Muller.
     */
    public function normal(float $mean = 0.0, float $stddev = 1.0): float
    {
        if ($stddev < 0.0) {
            throw new \InvalidArgumentException(__METHOD__ . ': stddev must be >= 0');
        }
        if ($stddev === 0.0) {
            return $mean;
        }

        $u1 = $this->uniformOpen();
        $u2 = $this->uniformOpen();
        $z = sqrt(-2.0 * log($u1)) * cos(2.0 * M_PI * $u2);

        return $mean + $stddev * $z;
    }

    /**
     * Sample an inter-arrival time from Exponential(rate).
     * Mean waiting time is 1/rate (same units as the rate reciprocal).
     *
     * @param  float $rate  Intensity λ > 0 (events per unit time)
     */
    public function exponential(float $rate): float
    {
        if ($rate <= 0.0) {
            throw new \InvalidArgumentException(__METHOD__ . ': rate must be > 0');
        }

        return -log($this->uniformOpen()) / $rate;
    }

    /**
     * Sample from Poisson(lambda): count of events with mean lambda.
     * Useful for “how many bot actions this tick?” with mean ≈ lambda.
     *
     * @param  float $lambda  Expected event count (>= 0)
     */
    public function poisson(float $lambda): int
    {
        if ($lambda < 0.0) {
            throw new \InvalidArgumentException(__METHOD__ . ': lambda must be >= 0');
        }
        if ($lambda === 0.0) {
            return 0;
        }

        // Knuth algorithm — fine for typical small rates (e.g. bot ticks).
        if ($lambda < 30.0) {
            $L = exp(-$lambda);
            $k = 0;
            $p = 1.0;
            do {
                $k++;
                $p *= $this->uniformOpen();
            } while ($p > $L);

            return $k - 1;
        }

        // Normal approximation for larger means.
        $sample = $this->normal($lambda, sqrt($lambda)) + 0.5;

        return max(0, (int) floor($sample));
    }

    /**
     * Expected event count for a homogeneous Poisson process over a duration.
     * Equivalent to poisson(rate * duration).
     *
     * @param  float $rate      Events per unit time (>= 0)
     * @param  float $duration  Length of the interval (>= 0)
     */
    public function poissonCount(float $rate, float $duration): int
    {
        if ($rate < 0.0) {
            throw new \InvalidArgumentException(__METHOD__ . ': rate must be >= 0');
        }
        if ($duration < 0.0) {
            throw new \InvalidArgumentException(__METHOD__ . ': duration must be >= 0');
        }

        return $this->poisson($rate * $duration);
    }

    /**
     * Simulate a homogeneous Poisson process on [t0, t0 + duration).
     * Returns ascending event timestamps (empty when rate or duration is 0).
     *
     * @param  float $rate      Events per unit time (>= 0)
     * @param  float $duration  Length of the interval (>= 0)
     * @param  float $t0        Start time of the interval
     * @return list<float>
     */
    public function poissonProcess(float $rate, float $duration, float $t0 = 0.0): array
    {
        if ($rate < 0.0) {
            throw new \InvalidArgumentException(__METHOD__ . ': rate must be >= 0');
        }
        if ($duration < 0.0) {
            throw new \InvalidArgumentException(__METHOD__ . ': duration must be >= 0');
        }
        if ($rate === 0.0 || $duration === 0.0) {
            return [];
        }

        $events = [];
        $t = $t0;
        $end = $t0 + $duration;

        while (true) {
            $t += $this->exponential($rate);
            if ($t >= $end) {
                break;
            }
            $events[] = $t;
        }

        return $events;
    }

}
