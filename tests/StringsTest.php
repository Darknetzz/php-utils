<?php

namespace PHPUtils\Tests;

use PHPUnit\Framework\TestCase;
use PHPUtils\Strings;

/**
 * Test class for Strings
 */
class StringsTest extends TestCase
{
    private Strings $strings;

    protected function setUp(): void
    {
        $this->strings = new Strings();
    }

    public function testSlugify()
    {
        $result = $this->strings->slugify('Hello World');
        $this->assertEquals('hello_world', $result);
    }

    public function testSlugifyWithCustomSeparator()
    {
        $result = $this->strings->slugify('Hello World', '-');
        $this->assertEquals('hello-world', $result);
    }

    public function testSlugifyWithSpecialCharacters()
    {
        $result = $this->strings->slugify('Hello! World@ Test#');
        $this->assertEquals('hello_world_test', $result);
    }

    public function testSlugifyWithLengthCap()
    {
        $result = $this->strings->slugify('This is a very long string', '_', 10);
        $this->assertLessThanOrEqual(10, strlen($result));
    }

    public function testSlugifyRemovesMultipleUnderscores()
    {
        $result = $this->strings->slugify('Hello   World');
        $this->assertEquals('hello_world', $result);
    }

    public function testCap()
    {
        $longString = 'This is a very long string that should be capped';
        $result = $this->strings->cap($longString, 30);
        
        $this->assertLessThanOrEqual(33, strlen($result)); // 30 + "..."
        $this->assertStringEndsWith('...', $result);
    }

    public function testCapWithDifferentMaxLength()
    {
        $longString = 'This is a very long string that should be capped';
        $result = $this->strings->cap($longString, 20);
        
        // Should be exactly 20 characters + "..." = 23 total
        $this->assertEquals(23, strlen($result));
        $this->assertStringEndsWith('...', $result);
        $this->assertEquals('This is a very long ...', $result);
    }

    public function testCapWithVeryShortMaxLength()
    {
        $longString = 'This is a test';
        $result = $this->strings->cap($longString, 5);
        
        // Should be exactly 5 characters + "..." = 8 total
        $this->assertEquals(8, strlen($result));
        $this->assertEquals('This ...', $result);
    }

    public function testCapWithShortString()
    {
        $shortString = 'Short';
        $result = $this->strings->cap($shortString, 30);
        
        $this->assertEquals('Short', $result);
        $this->assertStringNotContainsString('...', $result);
    }

    public function testAppendGetParamsToUrl()
    {
        $url = 'https://example.com/page';
        $params = ['key' => 'value', 'test' => '123'];
        
        $result = $this->strings->appendGetParamsToUrl($url, $params);
        
        $this->assertStringContainsString('key=value', $result);
        $this->assertStringContainsString('test=123', $result);
        $this->assertStringStartsWith('https://example.com/page?', $result);
    }

    public function testAppendGetParamsToUrlWithExistingParams()
    {
        $url = 'https://example.com/page?existing=1';
        $params = ['new' => '2'];
        
        $result = $this->strings->appendGetParamsToUrl($url, $params);
        
        $this->assertStringContainsString('existing=1', $result);
        $this->assertStringContainsString('new=2', $result);
    }

    public function testAppendGetParamsToUrlOverwritesExisting()
    {
        $url = 'https://example.com/page?key=old';
        $params = ['key' => 'new'];
        
        $result = $this->strings->appendGetParamsToUrl($url, $params);
        
        $this->assertStringContainsString('key=new', $result);
        $this->assertStringNotContainsString('key=old', $result);
    }

    public function testAppendGetParamsToUrlWithComplexUrl()
    {
        $url = 'https://user:pass@example.com:8080/path?existing=1#fragment';
        $params = ['new' => '2'];
        
        $result = $this->strings->appendGetParamsToUrl($url, $params);
        
        $this->assertStringContainsString('example.com', $result);
        $this->assertStringContainsString('new=2', $result);
        $this->assertStringContainsString('#fragment', $result);
    }
}

