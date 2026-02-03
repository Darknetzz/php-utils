<?php

declare(strict_types=1);

namespace PHPUtils\Tests;

use PHPUnit\Framework\TestCase;
use PHPUtils\Files;

/**
 * Test class for Files
 */
class FilesTest extends TestCase
{
    private Files $files;

    protected function setUp(): void
    {
        $this->files = new Files();
    }

    public function testIsFileWithExistingFile(): void
    {
        $result = $this->files->is_file(__FILE__);
        $this->assertTrue($result);
    }

    public function testIsFileWithNonExistentPath(): void
    {
        $result = $this->files->is_file(__DIR__ . '/nonexistent_file_12345.php');
        $this->assertFalse($result);
    }

    public function testIsFileWithDirectory(): void
    {
        $result = $this->files->is_file(__DIR__);
        $this->assertFalse($result);
    }

    public function testFileReadWithExistingFile(): void
    {
        $tmpFile = sys_get_temp_dir() . '/phputils_files_test_' . uniqid() . '.txt';
        file_put_contents($tmpFile, 'hello world');
        try {
            $result = $this->files->file_read($tmpFile);
            $this->assertEquals('hello world', $result);
        } finally {
            @unlink($tmpFile);
        }
    }

    public function testFileReadWithNonExistentFileThrows(): void
    {
        $this->expectException(\Exception::class);
        $this->files->file_read(__DIR__ . '/nonexistent_12345.txt');
    }
}
