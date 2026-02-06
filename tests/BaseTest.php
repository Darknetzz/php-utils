<?php

declare(strict_types=1);

namespace PHPUtils\Tests;

use PHPUnit\Framework\TestCase;
use PHPUtils\Base;
use PHPUtils\Debugger;
use PHPUtils\Vars;

/**
 * Test class for Base
 */
class BaseTest extends TestCase
{
    public function testBaseIsAbstract()
    {
        $this->assertTrue(
            (new \ReflectionClass(Base::class))->isAbstract(),
            'Base class should be abstract'
        );
    }

    public function testBaseCannotBeInstantiated()
    {
        $this->expectException(\Error::class);
        new Base();
    }

    public function testBaseProperties()
    {
        // Create a concrete class that extends Base for testing
        $testClass = new class extends Base {
            public function getDebugger() { return $this->debugger; }
            public function getVars() { return $this->vars; }
            public function getVerbose() { return $this->verbose; }
        };

        $this->assertInstanceOf(Debugger::class, $testClass->getDebugger());
        $this->assertInstanceOf(Vars::class, $testClass->getVars());
        $this->assertIsBool($testClass->getVerbose());
    }

    public function testBaseWithDependencyInjection()
    {
        $customDebugger = new Debugger(true);
        $customVars = new Vars();

        $testClass = new class($customDebugger, $customVars) extends Base {
            public function getDebugger() { return $this->debugger; }
            public function getVars() { return $this->vars; }
        };

        $this->assertSame($customDebugger, $testClass->getDebugger());
        $this->assertSame($customVars, $testClass->getVars());
    }
}

