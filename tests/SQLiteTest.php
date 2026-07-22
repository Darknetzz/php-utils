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

    public function testCleanStripsNewlinesAndEncodes(): void
    {
        $input = "  hello\n\rworld  ";
        $result = $this->sqlite->clean($input);
        $this->assertStringNotContainsString("\n", $result);
        $this->assertStringNotContainsString("\r", $result);
        $this->assertNotEmpty($result);
    }

    public function testSqliteCreateSelectAndDropDb(): void
    {
        $tmpDir = sys_get_temp_dir();
        $dbname = $tmpDir . '/phputils_sqlite_test_' . uniqid();

        $db = $this->sqlite->sqlite_create_db($dbname);
        $this->assertInstanceOf(\SQLite3::class, $db);
        $db->close();

        $selected = $this->sqlite->sqlite_select_db($dbname);
        $this->assertInstanceOf(\SQLite3::class, $selected);
        $selected->close();

        $this->assertTrue($this->sqlite->sqlite_drop_db($dbname));
    }

    public function testSqliteSelectDbThrowsWhenMissing(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->sqlite->sqlite_select_db(sys_get_temp_dir() . '/phputils_missing_' . uniqid());
    }

    public function testSqliteTableLifecycle(): void
    {
        $tmpDir = sys_get_temp_dir();
        $dbname = $tmpDir . '/phputils_sqlite_table_' . uniqid();
        $db = $this->sqlite->sqlite_create_db($dbname);

        try {
            $this->assertFalse($this->sqlite->sqlite_table_exists($db, 'items'));

            $this->sqlite->sqlite_create_table($db, 'items', [
                'name' => 'TEXT',
                'qty' => 'INTEGER',
            ]);
            $this->assertTrue($this->sqlite->sqlite_table_exists($db, 'items'));

            $this->sqlite->sqlite_insert($db, 'items', ['name' => 'widget', 'qty' => 3]);
            $result = $this->sqlite->sqlite_select($db, 'items');
            $row = $result->fetchArray(SQLITE3_ASSOC);
            $this->assertSame('widget', $row['name']);
            $this->assertSame(3, $row['qty']);
            $result->finalize();

            $this->assertTrue($this->sqlite->sqlite_update($db, 'items', ['qty' => 5], 'name = ?', ['widget']));
            $this->assertTrue($this->sqlite->sqlite_delete($db, 'items', 'name = ?', ['widget']));

            $tables = $this->sqlite->sqlite_get_tables($db);
            $this->assertContains('items', $tables);

            $columns = $this->sqlite->sqlite_get_columns($db, 'items');
            $names = array_column($columns, 'name');
            $this->assertContains('id', $names);
            $this->assertContains('name', $names);

            $this->sqlite->sqlite_drop_table($db, 'items');
            $this->assertFalse($this->sqlite->sqlite_table_exists($db, 'items'));
        } finally {
            $db->close();
            $this->sqlite->sqlite_drop_db($dbname);
        }
    }

    public function testSqliteCreateTableThrowsIfExists(): void
    {
        $tmpDir = sys_get_temp_dir();
        $dbname = $tmpDir . '/phputils_sqlite_dup_' . uniqid();
        $db = $this->sqlite->sqlite_create_db($dbname);

        try {
            $this->sqlite->sqlite_create_table($db, 'dup', ['x' => 'TEXT']);
            $this->expectException(\RuntimeException::class);
            $this->sqlite->sqlite_create_table($db, 'dup', ['x' => 'TEXT']);
        } finally {
            $db->close();
            if (file_exists($dbname . '.sqlite')) {
                $this->sqlite->sqlite_drop_db($dbname);
            }
        }
    }

    public function testSqliteVersion(): void
    {
        $version = $this->sqlite->sqlite_version();
        $this->assertIsArray($version);
        $this->assertArrayHasKey('versionString', $version);
        $this->assertIsString($this->sqlite->sqlite_version('versionString'));
    }

    public function testSqliteFunctionDispatches(): void
    {
        $version = $this->sqlite->sqlite_function('version', 'versionString');
        $this->assertIsString($version);
    }

    public function testSqliteFunctionThrowsForUnknownMethod(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sqlite->sqlite_function('no_such_method');
    }
}
