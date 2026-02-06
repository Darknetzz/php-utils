<?php

declare(strict_types=1);

namespace PHPUtils\Tests;

use PHPUnit\Framework\TestCase;
use PHPUtils\Random;

/**
 * Test class for Random
 */
class RandomTest extends TestCase
{
    private Random $random;

    protected function setUp(): void
    {
        $this->random = new Random();
    }

    public function testArrayPickRandomReturnsElementFromArray(): void
    {
        $arr = ['a', 'b', 'c'];
        $result = $this->random->array_pick_random($arr);
        $this->assertContains($result, $arr);
    }

    public function testArrayPickRandomWithSingleElement(): void
    {
        $arr = ['only'];
        $result = $this->random->array_pick_random($arr);
        $this->assertEquals('only', $result);
    }

    public function testRollReturnsIntInRange(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $result = $this->random->roll(1, 10);
            $this->assertGreaterThanOrEqual(1, $result);
            $this->assertLessThanOrEqual(10, $result);
        }
    }

    public function testRollWithCustomRange(): void
    {
        $result = $this->random->roll(5, 5);
        $this->assertEquals(5, $result);
    }

    public function testPercentageReturnsBoolean(): void
    {
        $result = $this->random->percentage(50);
        $this->assertIsBool($result);
    }

    public function testPercentageAtZero(): void
    {
        $result = $this->random->percentage(0);
        $this->assertFalse($result);
    }

    public function testPercentageAtHundred(): void
    {
        $result = $this->random->percentage(100);
        $this->assertTrue($result);
    }

    public function testGenStrReturnsCorrectLength(): void
    {
        $result = $this->random->genStr(12);
        $this->assertIsString($result);
        $this->assertEquals(12, strlen($result));
    }
}
