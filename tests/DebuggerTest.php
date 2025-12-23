<?php

namespace PHPUtils\Tests;

use PHPUnit\Framework\TestCase;
use PHPUtils\Debugger;
use PHPUtils\Vars;

/**
 * Test class for Debugger
 */
class DebuggerTest extends TestCase
{
    private Debugger $debugger;

    protected function setUp(): void
    {
        $this->debugger = new Debugger(true);
    }

    public function testConstructor()
    {
        $debugger = new Debugger(false);
        $this->assertFalse($debugger->verbose);
        
        $debugger2 = new Debugger(true);
        $this->assertTrue($debugger2->verbose);
    }

    public function testConstructorWithDependencyInjection()
    {
        $vars = new Vars();
        $debugger = new Debugger(true, $vars);
        
        $this->assertTrue($debugger->verbose);
    }

    public function testFormatData()
    {
        $data = $this->debugger->formatData('test message', 'info');
        
        $this->assertIsArray($data);
        $this->assertEquals('info', $data['type']);
        $this->assertArrayHasKey('icon', $data);
        $this->assertArrayHasKey('header', $data);
        $this->assertArrayHasKey('body', $data);
    }

    public function testFormatDataWithArray()
    {
        $input = ['title', 'body content'];
        $data = $this->debugger->formatData($input, 'info');
        
        $this->assertIsArray($data);
        $this->assertStringContainsString('title', $data['header']);
    }

    public function testFormatDataWithEmptyInput()
    {
        $data = $this->debugger->formatData('', 'info');
        $this->assertEquals('[empty]', $data['body']);
    }

    public function testFormatDataTypes()
    {
        $types = ['info', 'danger', 'warning', 'success'];
        
        foreach ($types as $type) {
            $data = $this->debugger->formatData('test', $type);
            $this->assertEquals($type, $data['type']);
            $this->assertNotNull($data['icon']);
        }
    }

    public function testFormatAsHtml()
    {
        $data = [
            'type' => 'info',
            'icon' => 'ℹ️',
            'header' => 'info Test',
            'body' => 'test message'
        ];
        
        $html = $this->debugger->formatAsHtml($data);
        
        $this->assertIsString($html);
        $this->assertStringContainsString('alert', $html);
        $this->assertStringContainsString('alert-info', $html);
        $this->assertStringContainsString('test message', $html);
    }

    public function testFormat()
    {
        $html = $this->debugger->format('test message', 'info');
        
        $this->assertIsString($html);
        $this->assertStringContainsString('alert', $html);
        $this->assertStringContainsString('test message', $html);
    }

    public function testOutput()
    {
        ob_start();
        $this->debugger->output('test message', 'info', false);
        $output = ob_get_clean();
        
        $this->assertStringContainsString('test message', $output);
    }

    public function testDebugLog()
    {
        $debugArray = [];
        $this->debugger->debug_log($debugArray, 'test message', 'Test Title');
        
        $this->assertCount(1, $debugArray);
        $this->assertEquals('test message', $debugArray[0]['message']);
        $this->assertEquals('Test Title', $debugArray[0]['title']);
        $this->assertArrayHasKey('timestamp', $debugArray[0]);
    }

    public function testDebugLogWithVerboseFalse()
    {
        $debugger = new Debugger(false);
        $debugArray = [];
        $result = $debugger->debug_log($debugArray, 'test message');
        
        $this->assertNull($result);
        $this->assertCount(0, $debugArray);
    }

    public function testDebugPrint()
    {
        $debugArray = [
            [
                'timestamp' => '12:00:00',
                'title' => 'Test',
                'message' => 'Test message'
            ]
        ];
        
        $html = $this->debugger->debug_print($debugArray, 'Debug Log');
        
        $this->assertIsString($html);
        $this->assertStringContainsString('Debug Log', $html);
        $this->assertStringContainsString('Test message', $html);
    }

    public function testThrowException()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('test error');
        
        $this->debugger->throw_exception('test error');
    }
}

