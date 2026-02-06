<?php

declare(strict_types=1);

namespace PHPUtils\Tests;

use PHPUnit\Framework\TestCase;
use PHPUtils\Times;

/**
 * Test class for Times
 */
class TimesTest extends TestCase
{
    private Times $times;

    protected function setUp(): void
    {
        $this->times = new Times();
    }

    public function testGetCurrentTimeReturnsFormattedString(): void
    {
        $result = $this->times->getCurrentTime('Y-m-d', 'UTC');
        $this->assertIsString($result);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $result);
    }

    public function testGetCurrentTimeWithTimezone(): void
    {
        $utc = $this->times->getCurrentTime('H:i', 'UTC');
        $europe = $this->times->getCurrentTime('H:i', 'Europe/London');
        $this->assertIsString($utc);
        $this->assertIsString($europe);
    }

    public function testRelativeTimeWithFormat(): void
    {
        $past = (new \DateTime('-2 days'))->format('Y-m-d H:i:s');
        $result = $this->times->relativeTime($past, 'days');
        $this->assertIsString($result);
        $this->assertEquals('2', $result);
    }

    public function testRelativeTimeReturnsString(): void
    {
        $past = (new \DateTime('-1 hour'))->format('Y-m-d H:i:s');
        $result = $this->times->relativeTime($past);
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }
}
