<?php

# ──────────────────────────────────────────────────────────────────────────────────────────────── #
#                                               BASE                                               #
# ──────────────────────────────────────────────────────────────────────────────────────────────── #
class Base {

    public $name = "PHPUtils";
    public $author = "Darknetzz";
    public $url    = "https://github.com/Darknetzz";
    public $version = "1.0.0";

    public $debug_log = [];


    public function printInfo() {
        return "$name - $author - $version";
    }

    public function __construct() {

    }

        
    /**
     * debug
     * Adds an entry to the $debug_log array
     *
     * @param  mixed $debug_log
     * @param  mixed $txt
     * @return void
     */
    public function debug($txt) {

        if (!isset($debug_log)) {
            $debug_log = [];
        }

        $debugger = new Debugger();
        $debugger->debug_log($debug_log, $txt);

        return $debug_log;

    }

}