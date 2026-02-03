<?php

declare(strict_types=1);

namespace PHPUtils;

/* ────────────────────────────────────────────────────────────────────────── */
/*                                   VarUtil                                  */
/* ────────────────────────────────────────────────────────────────────────── */
/* 
    This class will contain different utilities for handling variables of all kinds.
*/

class Vars {
    
    /* ───────────────────────────────────────────────────────────────────── */
    /*                               var_assert                              */
    /* ───────────────────────────────────────────────────────────────────── */
    public function var_assert(mixed &$var, mixed $assertVal = false, bool $lazy = false) : bool {
        if (!isset($var)) {
            return false;
        }
    
        if ($assertVal != false || func_num_args() > 1) {
    
            if ($lazy != false) {
                return $var == $assertVal;
            }
    
            return $var === $assertVal;
        }
        
        return true;
    }


    /* ───────────────────────────────────────────────────────────────────── */
    /*                             arrayInString                             */
    /* ───────────────────────────────────────────────────────────────────── */
    /**
     * Check whether any element of the array contains the needle as a substring.
     *
     * @param array $haystack Array of strings to search in
     * @param string $needle Substring to look for
     * @return bool True if any element contains the needle, false otherwise
     */
    public function arrayInString(array $haystack, string $needle): bool {
        foreach ($haystack as $char) {
            if (strpos($char, $needle) !== FALSE) {
                return true;
            }
        }
        return false;
    }

    /**
     * Alias for arrayInString. Returns true if any element of the array contains the needle as a substring.
     */
    public function arrayContainsSubstring(array $haystack, string $needle): bool {
        return $this->arrayInString($haystack, $needle);
    }

    /* ───────────────────────────────────────────────────────────────────── */
    /*                               stringify                               */
    /* ───────────────────────────────────────────────────────────────────── */
    public function stringify(mixed $var) {

        $return = $var;

        if (is_array($var)) {

            $return = json_encode($return, JSON_PRETTY_PRINT);

        }

        return $return;
    }


    /* ───────────────────────────────────────────────────────────────────── */
    /*                              in_md_array                              */
    /* ───────────────────────────────────────────────────────────────────── */
    # NOTE: This function might be reesource expensive, but at least it works now.
    public function in_md_array(array $haystack, string $needle) {

        $contains = False;

        # Callback function
        $callBack = function($val, $key, $needle) use(&$contains) {

            # We have already found what we are looking for
            if ($contains === True) {
                return $contains;
            }

            # Found it!
            if ($key == $needle || $val == $needle) {
                $contains = True;
            }

            return $contains;
        };

        array_walk_recursive($haystack, $callBack, $needle);

        if ($contains !== False) {
            return True;
        }
        return False;
    }

}