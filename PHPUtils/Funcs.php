<?php

declare(strict_types=1);

namespace PHPUtils;

/**
 * Funcs
 *
 * @author Darknetzz
 * @package PHPUtils
 * @version 1.0.0
 * @since 1.0.0
 * @license MIT
 * 
 * A class to handle functions
 */
class Funcs extends Base {
    /**
     * Counts the number of parameters of a given function.
     *
     * @param string $functionName The name of the function.
     * @return int The number of parameters of the function.
     */
    public function countFunctionParams(string $functionName) : int {
        $reflection = new \ReflectionFunction($functionName);
        $paramCount = $reflection->getNumberOfParameters();
        return $paramCount;
    }
}