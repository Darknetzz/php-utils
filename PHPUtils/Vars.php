<?php

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
    public function arrayInString(array $haystack, string $needle) {
        foreach ($haystack as $char) {
            if (strpos($char, $needle) !== FALSE) {
                return true;
            }
        }
        return false;
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