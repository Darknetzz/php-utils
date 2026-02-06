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

}