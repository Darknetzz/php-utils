<?php

namespace PHPUtils\Tests;

use PHPUnit\Framework\TestCase;
use PHPUtils\SQL;
use mysqli;

/**
 * Test class for SQL
 */
class SQLTest extends TestCase
{
    private ?SQL $sql;
    private ?mysqli $connection;

    protected function setUp(): void
    {
        $this->sql = new SQL();
        $this->connection = null;
        
        // Try to create a connection, but don't fail if MySQL isn't available
        // Tests will skip themselves if connection is needed
        try {
            $this->connection = @new mysqli('localhost', 'test', 'test', '');
            if ($this->connection && $this->connection->connect_error) {
                $this->connection = null;
            }
        } catch (\Exception $e) {
            $this->connection = null;
        }
    }

    protected function tearDown(): void
    {
        if ($this->connection && !$this->connection->connect_error) {
            $this->connection->close();
        }
    }

    public function testSetConnection()
    {
        // Create a mock connection for this test
        $mockConnection = $this->createMock(mysqli::class);
        $this->sql->setConnection($mockConnection);
        $this->assertSame($mockConnection, $this->sql->getConnection());
    }

    public function testConnectHost()
    {
        // This test requires actual MySQL server
        $this->markTestSkipped('Requires MySQL server');
        
        try {
            $conn = $this->sql->connectHost('localhost', 'test', 'test');
            $this->assertInstanceOf(mysqli::class, $conn);
        } catch (\RuntimeException $e) {
            $this->markTestSkipped('MySQL connection failed: ' . $e->getMessage());
        }
    }

    public function testConnectDB()
    {
        // This test requires actual MySQL server
        $this->markTestSkipped('Requires MySQL server');
        
        try {
            $conn = $this->sql->connectDB('localhost', 'test', 'test', 'test_db');
            $this->assertInstanceOf(mysqli::class, $conn);
        } catch (\RuntimeException $e) {
            $this->markTestSkipped('MySQL connection failed: ' . $e->getMessage());
        }
    }

    public function testExecuteQueryWithoutConnectionThrowsException()
    {
        $sql = new SQL();
        
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Database connection not set');
        
        $sql->executeQuery("SELECT 1");
    }

    public function testExecuteQueryWithConnection()
    {
        if (!$this->connection || $this->connection->connect_error) {
            $this->markTestSkipped('MySQL connection not available');
        }

        $this->sql->setConnection($this->connection);
        
        // Create a test table
        $this->connection->query("CREATE TEMPORARY TABLE test_table (id INT, name VARCHAR(50))");
        
        $result = $this->sql->executeQuery("SELECT 1 as test");
        $this->assertInstanceOf(\mysqli_result::class, $result);
        
        $row = $result->fetch_assoc();
        $this->assertEquals(1, $row['test']);
    }

    public function testExecuteQueryWithParams()
    {
        if (!$this->connection || $this->connection->connect_error) {
            $this->markTestSkipped('MySQL connection not available');
        }

        $this->sql->setConnection($this->connection);
        $this->connection->query("CREATE TEMPORARY TABLE test_table (id INT, name VARCHAR(50))");
        
        $result = $this->sql->executeQuery("SELECT ? as test", [1]);
        $row = $result->fetch_assoc();
        $this->assertEquals('1', $row['test']); // Prepared statements return strings
    }

    public function testExecuteQueryReturnId()
    {
        if (!$this->connection || $this->connection->connect_error) {
            $this->markTestSkipped('MySQL connection not available');
        }

        $this->sql->setConnection($this->connection);
        $this->connection->query("CREATE TEMPORARY TABLE test_table (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(50))");
        
        $this->connection->query("INSERT INTO test_table (name) VALUES ('test')");
        $result = $this->sql->executeQuery("INSERT INTO test_table (name) VALUES (?)", ['test2'], 'id');
        
        $this->assertIsInt($result);
        $this->assertGreaterThan(0, $result);
    }

    public function testExecuteQueryInvalidReturnType()
    {
        if (!$this->connection || $this->connection->connect_error) {
            $this->markTestSkipped('MySQL connection not available');
        }

        $this->sql->setConnection($this->connection);
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid return type');
        
        $this->sql->executeQuery("SELECT 1", [], 'invalid');
    }

    public function testSaveResult()
    {
        if (!$this->connection || $this->connection->connect_error) {
            $this->markTestSkipped('MySQL connection not available');
        }

        $this->sql->setConnection($this->connection);
        $this->connection->query("CREATE TEMPORARY TABLE test_table (id INT, name VARCHAR(50))");
        $this->connection->query("INSERT INTO test_table VALUES (1, 'test1'), (2, 'test2')");
        
        $result = $this->sql->executeQuery("SELECT * FROM test_table");
        $saved = $this->sql->save_result($result);
        
        $this->assertIsArray($saved);
        $this->assertCount(2, $saved);
        $this->assertEquals('test1', $saved[1]['name']);
    }

    public function testError()
    {
        if (!$this->connection) {
            $this->markTestSkipped('MySQL connection not available');
        }
        
        $this->sql->setConnection($this->connection);
        $error = $this->sql->error();
        $this->assertIsString($error);
    }

    public function testConstants()
    {
        // Use reflection to access private constants
        $reflection = new \ReflectionClass(SQL::class);
        $returnResult = $reflection->getConstant('RETURN_RESULT');
        $returnId = $reflection->getConstant('RETURN_ID');
        
        $this->assertEquals('result', $returnResult);
        $this->assertEquals('id', $returnId);
    }

    public function testCountRowsWithInvalidTableName()
    {
        if (!$this->connection || $this->connection->connect_error) {
            $this->markTestSkipped('MySQL connection not available');
        }

        $this->sql->setConnection($this->connection);
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid table name');
        
        // Table name with SQL injection attempt
        $this->sql->countRows('users` WHERE 1=1; DROP TABLE users; --');
    }

    public function testCountRowsWithInvalidColumnName()
    {
        if (!$this->connection || $this->connection->connect_error) {
            $this->markTestSkipped('MySQL connection not available');
        }

        $this->sql->setConnection($this->connection);
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid column name');
        
        // Column name with SQL injection attempt
        $this->sql->countRows('users', 'id` OR 1=1; --', '1');
    }

    public function testCountRowsWithValidIdentifiers()
    {
        if (!$this->connection || $this->connection->connect_error) {
            $this->markTestSkipped('MySQL connection not available');
        }

        $this->sql->setConnection($this->connection);
        $this->connection->query("CREATE TEMPORARY TABLE test_users (id INT, username VARCHAR(50))");
        $this->connection->query("INSERT INTO test_users VALUES (1, 'alice'), (2, 'bob')");
        
        // Test valid table name
        $count = $this->sql->countRows('test_users');
        $this->assertEquals(2, $count);
        
        // Test valid table and column names with filter
        $count = $this->sql->countRows('test_users', 'username', 'alice');
        $this->assertEquals(1, $count);
    }

    public function testGetUniqueRowsWithInvalidTableName()
    {
        if (!$this->connection || $this->connection->connect_error) {
            $this->markTestSkipped('MySQL connection not available');
        }

        $this->sql->setConnection($this->connection);
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid table name');
        
        // Table name with SQL injection attempt
        $this->sql->getUniqueRows('users`; DROP TABLE users; --', 'username');
    }

    public function testGetUniqueRowsWithInvalidColumnName()
    {
        if (!$this->connection || $this->connection->connect_error) {
            $this->markTestSkipped('MySQL connection not available');
        }

        $this->sql->setConnection($this->connection);
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid column name');
        
        // Column name with SQL injection attempt
        $this->sql->getUniqueRows('users', 'username` UNION SELECT password FROM admin; --');
    }

    public function testGetUniqueRowsWithValidIdentifiers()
    {
        if (!$this->connection || $this->connection->connect_error) {
            $this->markTestSkipped('MySQL connection not available');
        }

        $this->sql->setConnection($this->connection);
        $this->connection->query("CREATE TEMPORARY TABLE test_products (id INT, category VARCHAR(50))");
        $this->connection->query("INSERT INTO test_products VALUES (1, 'electronics'), (2, 'electronics'), (3, 'books')");
        
        // Test valid identifiers
        $categories = $this->sql->getUniqueRows('test_products', 'category');
        $this->assertIsArray($categories);
        $this->assertCount(2, $categories);
        $this->assertContains('electronics', $categories);
        $this->assertContains('books', $categories);
    }

    public function testValidateIdentifierRejectsSpecialCharacters()
    {
        if (!$this->connection || $this->connection->connect_error) {
            $this->markTestSkipped('MySQL connection not available');
        }

        $this->sql->setConnection($this->connection);
        
        // Test various SQL injection patterns
        $invalidIdentifiers = [
            'table; DROP TABLE users;',
            'table` OR 1=1 --',
            'table/*comment*/',
            'table\\x00',
            "table'",
            'table"',
            'table`',
            'table(',
            'table)',
            'table;',
            'table ',
            'table\n',
            'table\t',
        ];
        
        foreach ($invalidIdentifiers as $invalid) {
            try {
                $this->sql->countRows($invalid);
                $this->fail("Expected InvalidArgumentException for identifier: $invalid");
            } catch (\InvalidArgumentException $e) {
                $this->assertStringContainsString('Invalid table name', $e->getMessage());
            }
        }
    }

    public function testValidateIdentifierAcceptsValidNames()
    {
        if (!$this->connection || $this->connection->connect_error) {
            $this->markTestSkipped('MySQL connection not available');
        }

        $this->sql->setConnection($this->connection);
        
        // Create temp tables with valid names
        $validIdentifiers = [
            'users',
            'user_data',
            'UserData',
            'user123',
            'data_2024',
        ];
        
        foreach ($validIdentifiers as $valid) {
            $tableName = 'test_' . $valid;
            $this->connection->query("CREATE TEMPORARY TABLE `$tableName` (id INT)");
            
            try {
                $count = $this->sql->countRows($tableName);
                $this->assertIsInt($count);
            } catch (\InvalidArgumentException $e) {
                $this->fail("Unexpected InvalidArgumentException for valid identifier: $valid - " . $e->getMessage());
            }
        }
    }

    public function testValidateIdentifierRejectsNumericStart()
    {
        if (!$this->connection || $this->connection->connect_error) {
            $this->markTestSkipped('MySQL connection not available');
        }

        $this->sql->setConnection($this->connection);
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('cannot start with a number');
        
        // Table name starting with a number
        $this->sql->countRows('123users');
    }
}

