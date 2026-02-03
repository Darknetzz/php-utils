<?php

declare(strict_types=1);

namespace PHPUtils\Tests;

use PHPUnit\Framework\TestCase;
use PHPUtils\SQLite;

/**
 * Test class for SQLite
 */
class SQLiteTest extends TestCase
{
    private SQLite $sqlite;

    protected function setUp(): void
    {
        $this->sqlite = new SQLite();
    }

    public function testResReturnsArrayWithStatusAndData(): void
    {
        $result = $this->sqlite->res('SUCCESS', 'test data');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('status', $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('data_type', $result);
        $this->assertEquals('SUCCESS', $result['status']);
        $this->assertEquals('test data', $result['data']);
    }

    public function testResDefaultValues(): void
    {
        $result = $this->sqlite->res();
        $this->assertEquals('UNKNOWN', $result['status']);
        $this->assertEquals('No data.', $result['data']);
    }

    public function testCleanStripsNewlinesAndEncodes(): void
    {
        $input = "  hello\n\rworld  ";
        $result = $this->sqlite->clean($input);
        $this->assertStringNotContainsString("\n", $result);
        $this->assertStringNotContainsString("\r", $result);
        $this->assertNotEmpty($result);
    }

    public function testSqliteCreateDbAndSelectDb(): void
    {
        $tmpDir = sys_get_temp_dir();
        $dbname = $tmpDir . '/phputils_sqlite_test_' . uniqid();
        $create = $this->sqlite->sqlite_create_db($dbname);
        $this->assertEquals('SUCCESS', $create['status']);
        $this->assertInstanceOf(\SQLite3::class, $create['data']);

        $select = $this->sqlite->sqlite_select_db($dbname);
        $this->assertEquals('SUCCESS', $select['status']);

        $drop = $this->sqlite->sqlite_drop_db($dbname);
        $this->assertEquals('SUCCESS', $drop['status']);
    }
}
