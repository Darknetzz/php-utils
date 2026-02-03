<?php

declare(strict_types=1);

# ──────────────────────────────────────────────────────────────────────────────────────────────── #
#                                          INITIAL CHECKS                                          #
# ──────────────────────────────────────────────────────────────────────────────────────────────── #
if (PHP_MAJOR_VERSION < 8) {
    die("PHPUtils requires PHP 8.0 or later.");
}


# ──────────────────────────────────────────────────────────────────────────────────────────────── #
#                                           CLASS FOLDER                                           #
# ──────────────────────────────────────────────────────────────────────────────────────────────── #
$classFolder = dirname(__FILE__);





# ──────────────────────────────────────────────────────────────────────────────────────────────── #
#                                         ESSENTIAL CLASSES                                        #
# ──────────────────────────────────────────────────────────────────────────────────────────────── #
$essentialClasses = [
    "Vars",
    "Debugger",
    "Base",
    "Files",
];

foreach ($essentialClasses as $class) {
    
    $file = $classFolder.'/'.$class.'.php';

    if (!file_exists($file)) {
        die("PHPUtils requires file $file, but it doesn't exist.");
    }

    require_once($file);
}
# ──────────────────────────────────────────────────────────────────────────────────────────────── #



# ──────────────────────────────────────────────────────────────────────────────────────────────── #
#                                           INCLUDE FILES                                          #
# ──────────────────────────────────────────────────────────────────────────────────────────────── #
foreach (glob($classFolder.'/*.php') as $fileToInclude) {

    if (__FILE__ == $fileToInclude) {
        continue;
    }

    $className = basename($fileToInclude, '.php');

    require_once($fileToInclude);

    // Check for class with namespace
    $fullyQualifiedClassName = "PHPUtils\\" . $className;
    if (!class_exists($fullyQualifiedClassName) && !class_exists($className)) {
        continue;
    }

    # REVIEW: This is very likely an awful idea lmao
    // define("UTIL_".strtoupper($className), new $className);

}
# ──────────────────────────────────────────────────────────────────────────────────────────────── #