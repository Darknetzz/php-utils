<?php

declare(strict_types=1);

namespace PHPUtils;

# ──────────────────────────────────────────────────────────────────────────────────────────────── #
#                                               BASE                                               #
# ──────────────────────────────────────────────────────────────────────────────────────────────── #
/**
    * Base
    *
    * @package PHPUtils
    * @author Darknetzz
    * @version 1.0.0
    * @since 1.0.0
    * @license MIT
    *
    * A base class to handle common functionalities
    */
abstract class Base {

    public string $name = "PHPUtils";
    public string $author = "Darknetzz";
    public string $url = "https://github.com/Darknetzz";
    public string $version = "1.0.0";

    protected Debugger $debugger;
    public array $debug_log = [];
    protected Vars $vars;
    protected bool $verbose = true;

    /**
     * __construct
     * 
     * @param Debugger|null $debugger Optional Debugger instance for dependency injection
     * @param Vars|null $vars Optional Vars instance for dependency injection
     * @param bool $verbose Whether to enable verbose debugging
     */
    public function __construct(?Debugger $debugger = null, ?Vars $vars = null, bool $verbose = true) {
        $this->verbose = $verbose;
        $this->debugger = $debugger ?? new Debugger($this->verbose);
        $this->vars = $vars ?? new Vars();
    }

}