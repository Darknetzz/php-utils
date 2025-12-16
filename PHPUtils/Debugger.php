<?php

/* ────────────────────────────────────────────────────────────────────────── */
/*                                  Debugger                                  */
/* ────────────────────────────────────────────────────────────────────────── */
/*

Usage example:

$debug_messages = [];

$debugger = new Debugger;
$debugger->debug_log($debug_messages, "This is a debug message", "Title");
$debugger->debug_log($debug_messages, "This is another debug message");
$debugger->debug_print($debug_messages, "Debug log");

*/

/**
 * Debugger
 * 
 * A class to handle debugging and logging
 * 
 * @package PHPUtils
 */
class Debugger {

    public bool $verbose;
    private $vars;
        
    /**
     * __construct
     *
     * @param  mixed $verbose
     * @param  mixed $debug_array
     * @return void
     */
    function __construct(bool $verbose = false)
    {
        $this->verbose = $verbose;

        # Instantiate Vars class
        $this->vars     = new Vars();
    }


    /**
     * format
     *
     * @param  mixed $txt
     * @param  mixed $type
     * @param  mixed $icon
     * @return void
     */
    function format(mixed $input, string $type = 'info') {

        if (empty($input)) {
            $input = '[empty]';
        }

        if ($type == 'info') {
            $icon = 'ℹ️';
        }
        if ($type == 'danger') {
            $icon = '❌';
        }
        if ($type == 'warning') {
            $icon = '⚠️';
        }
        if ($type == 'success') {
            $icon = '✅';
        }

        $icon = (!empty($icon) ? $icon : null);

        # Defaults (for string)
        $header = $icon." ".$type;
        $body   = $input;

        if (is_array($input)) {
            if (count($input) == 2) {
                $header = $icon." ".$this->vars->stringify($input[0]);
                $header = $icon." ".json_encode($input[0]);
                $body   = json_encode($input[1]);
            }
            elseif (count($input) > 2) {
                $header = $icon." ".$type;
                $body   = json_encode($input, JSON_PRETTY_PRINT);
            }
        }

        return '
        <div class="alert alert-'.$type.'">
        <h4>'.$header.'</h4>
        <hr>
        '.$body.'
        </div>
        ';
    }

        
  
    /**
     * output
     * prints formatted output and echoes it
     *
     * @param  mixed $txt
     * @param  mixed $type
     * @param  mixed $die
     * @return void
     */
    public function output(mixed $txt, string $type = 'info', bool $die = false) {
        if ($die) {
            die($this->format($txt, $type));
        }
        echo $this->format($txt, $type);
    }


    /**
     * debug_log
     *
     * @param  mixed $debug_array
     * @param  mixed $txt
     * @param  mixed $title
     * @return void
     */
    public function debug_log(array &$debug_array, string $txt, ?string $title = Null) {

        if ($this->verbose === true) {
            $push = 
            [
                "timestamp" => date('H:i:s'),
                "title"   => $title,
                "message"   => $txt,
            ];
            array_push($debug_array, $push); 
        
            return $debug_array;
        }

        return null;
    }



    /**
     * debug_print
     *
     * @param  mixed $debug_array
     * @param  mixed $tableName
     * @return void
     */
    function debug_print(array &$debug_array, $tableName = "Debug") {

        $tableName_slug = strtolower($tableName);
        $tableName_slug = preg_replace('/[^A-Za-z0-9\-]/', '', $tableName_slug);

        $collapse_id    = "{$tableName_slug}_collapse";

        $debugTable = "
        <button class='btn btn-warning' type='button' data-bs-toggle='collapse' data-bs-target='#$collapse_id' aria-expanded='false' aria-controls='$collapse_id'>
        $tableName
        </button>

        <div class='collapse' id='$collapse_id'>
        <div class='alert alert-warning'>
        <h4>$tableName</h4>
        <table class='table table-warning'>
        ";
        foreach ($debug_array as $debug_data) {
            $timestamp = "<kbd>$debug_data[timestamp]</kbd>";

            $debugTable .= "
            <tr>
            <td>$timestamp</td>
            <td>
            <b style='color:darkblue;'>$debug_data[title]</b>
            <br>
            $debug_data[message]
            </td>
            </tr>
            ";
        }
        $debugTable .= "</table></div></div>";

        return $debugTable;
    }


    // ─────────────────────────────────────────────────────────────────────────────────────────────── #
    //                                         THROW_EXCEPTION                                         #
    // ─────────────────────────────────────────────────────────────────────────────────────────────── #
    function throw_exception($message) {
        throw new Exception($message);
    }

}

?>