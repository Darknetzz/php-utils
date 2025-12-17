<?php

namespace PHPUtils;

/* ────────────────────────────────────────────────────────────────────────── */
/*                                  Debugger                                  */
/* ────────────────────────────────────────────────────────────────────────── */

/**
 * Debugger
 * 
 * @package PHPUtils
 * @version 1.0.0
 * @since 1.0.0
 * @license MIT
 *
 * A class to handle debugging
 *
 * @example "$debug_messages = [];" Create an empty array to store debug messages
 * @example "$debugger = new Debugger;" Instantiate the debugger
 * @example "$debugger->debug_log($debug_messages, "This is a debug message", "Title");" Log a debug message
 * @example "$debugger->debug_log($debug_messages, "This is another debug message");" Log another debug message
 * @example "$debugger->debug_print($debug_messages, "Debug log");" Print the debug log
 */
class Debugger {

    public bool $verbose;
    private Vars $vars;
        
    /**
     * __construct
     *
     * @param  bool $verbose
     * @param  Vars|null $vars Optional Vars instance for dependency injection
     * @return void
     */
    public function __construct(bool $verbose = false, ?Vars $vars = null)
    {
        $this->verbose = $verbose;
        $this->vars = $vars ?? new Vars();
    }


    /**
     * format
     * 
     * Format debug output as HTML (legacy method for backward compatibility)
     *
     * @param  mixed $input
     * @param  string $type
     * @return string HTML formatted output
     */
    public function format(mixed $input, string $type = 'info'): string {
        $data = $this->formatData($input, $type);
        return $this->formatAsHtml($data);
    }
    
    /**
     * formatData
     * 
     * Format debug output as structured data (separated from presentation)
     *
     * @param  mixed $input
     * @param  string $type
     * @return array Structured data
     */
    public function formatData(mixed $input, string $type = 'info'): array {
        if (empty($input)) {
            $input = '[empty]';
        }

        $icons = [
            'info' => 'ℹ️',
            'danger' => '❌',
            'warning' => '⚠️',
            'success' => '✅'
        ];
        
        $icon = $icons[$type] ?? null;

        # Defaults (for string)
        $header = ($icon ? $icon . " " : "") . $type;
        $body = $input;

        if (is_array($input)) {
            if (count($input) == 2) {
                $header = ($icon ? $icon . " " : "") . json_encode($input[0]);
                $body = json_encode($input[1]);
            }
            elseif (count($input) > 2) {
                $header = ($icon ? $icon . " " : "") . $type;
                $body = json_encode($input, JSON_PRETTY_PRINT);
            }
        }

        return [
            'type' => $type,
            'icon' => $icon,
            'header' => $header,
            'body' => $body
        ];
    }
    
    /**
     * formatAsHtml
     * 
     * Convert structured debug data to HTML
     *
     * @param  array $data
     * @return string HTML formatted output
     */
    public function formatAsHtml(array $data): string {
        return '
        <div class="alert alert-'.$data['type'].'">
        <h4>'.$data['header'].'</h4>
        <hr>
        '.$data['body'].'
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
     * @param  array $debug_array
     * @param  string $tableName
     * @return string HTML formatted debug table
     */
    public function debug_print(array &$debug_array, string $tableName = "Debug"): string {

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
    /**
     * throw_exception
     * 
     * Throw an exception with the given message
     *
     * @param  string $message
     * @return void
     * @throws \Exception
     */
    public function throw_exception(string $message): void {
        throw new \Exception($message);
    }

}

?>
?>