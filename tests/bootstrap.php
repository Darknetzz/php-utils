<?php

// Bootstrap file for PHPUnit tests - use Composer autoload when available
$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
} else {
    require_once __DIR__ . '/../PHPUtils/_All.php';
}

