<?php

namespace PHPUtils;

/**
 * This class provides utility functions for including external resources in PHP applications.
 */
class Resources extends Base {

    /**
     * Includes the Bootstrap CSS and JS files from a CDN.
     *
     * @param string $version The version of Bootstrap to include. Default is '5.3.2'.
     * @return void
     */
    function Bootstrap(string $version = '5.3.2') {
        echo '
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@'.$version.'/dist/css/bootstrap.min.css" rel="stylesheet">

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5'.$version.'/dist/js/bootstrap.bundle.min.js"></script>
        ';
    }

    /**
     * Includes the jQuery JS file from a CDN.
     *
     * @param string $version The version of jQuery to include. Default is '3.7.1'.
     * @return void
     */
    function jQuery(string $version = '3.7.1') {
        echo '
        <script src="https://code.jquery.com/jquery-'.$version.'.min.js"></script>
        ';
    }
}