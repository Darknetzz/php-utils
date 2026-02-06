<?php

declare(strict_types=1);

namespace PHPUtils\Tests;

use PHPUnit\Framework\TestCase;
use PHPUtils\Vars;

/**
 * Test class for Vars
 */
class VarsTest extends TestCase
{
    private Vars $vars;

    protected function setUp(): void
    {
        $this->vars = new Vars();
    }

    public function testVarAssertWithUnsetVariable()
    {
        $result = $this->vars->var_assert($undefined);
        $this->assertFalse($result);
    }

    public function testVarAssertWithSetVariable()
    {
        $var = 'test';
        $result = $this->vars->var_assert($var);
        $this->assertTrue($result);
    }

    public function testVarAssertWithValueCheck()
    {
        $var = 'test';
        $result = $this->vars->var_assert($var, 'test');
        $this->assertTrue($result);
        
        $result2 = $this->vars->var_assert($var, 'different');
        $this->assertFalse($result2);
    }

    public function testVarAssertWithLazyComparison()
    {
        $var = '5';
        $result = $this->vars->var_assert($var, 5, true);
        $this->assertTrue($result); // '5' == 5
        
        $result2 = $this->vars->var_assert($var, 5, false);
        $this->assertFalse($result2); // '5' !== 5
    }

    public function testArrayInString()
    {
        $haystack = ['hello', 'world', 'test'];
        
        $this->assertTrue($this->vars->arrayInString($haystack, 'hello'));
        $this->assertTrue($this->vars->arrayInString($haystack, 'world'));
        $this->assertFalse($this->vars->arrayInString($haystack, 'notfound'));
    }

    public function testArrayInStringPartialMatch()
    {
        $haystack = ['hello world', 'test string'];
        
        $this->assertTrue($this->vars->arrayInString($haystack, 'hello'));
        $this->assertTrue($this->vars->arrayInString($haystack, 'test'));
        $this->assertFalse($this->vars->arrayInString($haystack, 'xyz'));
    }

    public function testStringifyWithString()
    {
        $result = $this->vars->stringify('test');
        $this->assertEquals('test', $result);
    }

    public function testStringifyWithArray()
    {
        $array = ['key' => 'value', 'number' => 123];
        $result = $this->vars->stringify($array);
        
        $this->assertIsString($result);
        $decoded = json_decode($result, true);
        $this->assertEquals($array, $decoded);
    }

    public function testInMultiDimensionalArray()
    {
        $haystack = [
            'level1' => [
                'level2' => [
                    'level3' => 'value'
                ],
                'key' => 'found'
            ],
            'top' => 'level'
        ];
        
        $this->assertTrue($this->vars->in_md_array($haystack, 'found'));
        $this->assertTrue($this->vars->in_md_array($haystack, 'value'));
        $this->assertTrue($this->vars->in_md_array($haystack, 'level'));
        $this->assertFalse($this->vars->in_md_array($haystack, 'notfound'));
    }

    public function testInMultiDimensionalArrayWithKeySearch()
    {
        $haystack = [
            'key1' => 'value1',
            'key2' => 'value2'
        ];
        
        $this->assertTrue($this->vars->in_md_array($haystack, 'key1'));
        $this->assertTrue($this->vars->in_md_array($haystack, 'value1'));
    }
}

