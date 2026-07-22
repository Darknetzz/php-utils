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

    public function testUniformOpenIsStrictlyBetweenZeroAndOne(): void
    {
        for ($i = 0; $i < 50; $i++) {
            $u = $this->random->uniformOpen();
            $this->assertGreaterThan(0.0, $u);
            $this->assertLessThan(1.0, $u);
        }
    }

    public function testExponentialRejectsNonPositiveRate(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->random->exponential(0.0);
    }

    public function testExponentialIsPositive(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $this->assertGreaterThan(0.0, $this->random->exponential(2.5));
        }
    }

    public function testPoissonRejectsNegativeLambda(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->random->poisson(-1.0);
    }

    public function testPoissonZeroIsZero(): void
    {
        $this->assertSame(0, $this->random->poisson(0.0));
    }

    public function testPoissonMeanIsNearLambda(): void
    {
        $lambda = 4.0;
        $n = 2000;
        $sum = 0;
        for ($i = 0; $i < $n; $i++) {
            $sum += $this->random->poisson($lambda);
        }
        $mean = $sum / $n;
        // Loose bound: sample mean should land near λ.
        $this->assertEqualsWithDelta($lambda, $mean, 0.4);
    }

    public function testPoissonCountMatchesPoissonOfRateTimesDuration(): void
    {
        $this->assertSame(0, $this->random->poissonCount(0.0, 10.0));
        $this->assertSame(0, $this->random->poissonCount(3.0, 0.0));
        $this->assertGreaterThanOrEqual(0, $this->random->poissonCount(0.5, 10.0));
    }

    public function testPoissonProcessReturnsSortedTimesInsideInterval(): void
    {
        $t0 = 10.0;
        $duration = 5.0;
        $events = $this->random->poissonProcess(3.0, $duration, $t0);

        $prev = $t0;
        foreach ($events as $t) {
            $this->assertGreaterThanOrEqual($prev, $t);
            $this->assertLessThan($t0 + $duration, $t);
            $prev = $t;
        }
    }

    public function testPoissonProcessEmptyWhenRateOrDurationZero(): void
    {
        $this->assertSame([], $this->random->poissonProcess(0.0, 10.0));
        $this->assertSame([], $this->random->poissonProcess(2.0, 0.0));
    }

    public function testNormalZeroStddevReturnsMean(): void
    {
        $this->assertSame(7.5, $this->random->normal(7.5, 0.0));
    }
}
