<?php

namespace PHPUtils;

/* ────────────────────────────────────────────────────────────────────────── */
/*                                 Strings                                    */
/* ────────────────────────────────────────────────────────────────────────── */

/**
 * Class Strings
 * 
 * A class to handle string manipulation
 */
class Strings extends Base {
    /**
    * slugify
    *
    * @param  string $string The string to slugify.
    * @param  string $replace The character to replace spaces with. Default is '_'.
    * @param  int $lenCap The maximum length of the slug. Default is 0.
    * @return string The slugified string
    */
    public function slugify(string $string, string $replace = '_', int $lenCap = 0): string {
        
        $search = [
            ' ', '-', '_', '.',
        ];
        
        $final_string = trim(strtolower($string));
        $final_string = str_replace($search, "_",  $final_string);
        $final_string = preg_replace('/[^A-Za-z0-9\_]/', '',   $final_string);
        $final_string = preg_replace('/_+/', '_', $final_string);
        $final_string = str_replace('_', $replace, $final_string);
        
        if (strlen($final_string) > $lenCap && $lenCap != 0) {
            $final_string = substr($final_string, 0, ($lenCap - 1));
        }
        
        return $final_string;
        
    }
    
    /**
    * hide
    *
    * @param  string $string The string to hide.
    * @param  int $visibility The number of characters to keep visible. Default is 3.
    * @return string|null The hidden string or null if invalid
    */
    public function hide(string $string, int $visibility = 3): ?string {
        
        $len = strlen($string);
        
        if ($len < 3 || $len - $visibility < 0) {
            $this->debugger->output("Unable to hide string.");
            return null;
        }
        
        if ($len < $visibility) {
            $this->debugger->output("Parameter string is longer than visibility");
            return null;
        }
        
        // TODO: Implement actual hiding logic
        return $string;
    }
    
    /**
     * cap
     * 
     * Cap a string to a certain length
     * 
     * @param  string $string The string to cap
     * @param  int $maxlen The maximum length of the string. Default is 30.
     * @return string The capped string
     */
    public function cap(string $string, int $maxlen = 30): string {
        if (strlen($string) > $maxlen) {
            return substr($string, 0, 30)."...";
        }
        return $string;
    }

    /**
     * appendGetParamsToUrl
     * 
     * Append GET parameters to a URL (written by Co-Pilot)
     * 
     * @param  string $url The URL to append the parameters to
     * @param  array $params The parameters to append
     * @return string The new URL
     */
    public function appendGetParamsToUrl(string $url, array $params = []): string {
        // Parse the URL into its components
        $urlComponents = parse_url($url);

        // If the URL already has a query string, parse it into an array
        $existingParams = [];
        if (isset($urlComponents['query'])) {
            parse_str($urlComponents['query'], $existingParams);
        }

        // Merge the existing parameters with the new ones
        $mergedParams = array_merge($existingParams, $params);

        // Build the new query string
        $newQueryString = http_build_query($mergedParams);

        // Rebuild the URL
        $newUrl = (isset($urlComponents['scheme']) ? $urlComponents['scheme'] . '://' : '')
            . (isset($urlComponents['user']) ? $urlComponents['user'] . (isset($urlComponents['pass']) ? ':' . $urlComponents['pass'] : '') . '@' : '')
            . (isset($urlComponents['host']) ? $urlComponents['host'] : '')
            . (isset($urlComponents['port']) ? ':' . $urlComponents['port'] : '')
            . (isset($urlComponents['path']) ? $urlComponents['path'] : '')
            . ($newQueryString ? '?' . $newQueryString : '')
            . (isset($urlComponents['fragment']) ? '#' . $urlComponents['fragment'] : '');

        return $newUrl;
    }
}