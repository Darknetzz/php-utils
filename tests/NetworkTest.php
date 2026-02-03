<?php

declare(strict_types=1);

namespace PHPUtils\Tests;

use PHPUnit\Framework\TestCase;
use PHPUtils\Network;

/**
 * Test class for Network
 */
class NetworkTest extends TestCase
{
    private Network $network;

    protected function setUp(): void
    {
        $this->network = new Network();
    }

    public function testCidrToRange()
    {
        $result = $this->network->cidrToRange('192.168.1.0/24');
        
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals('192.168.1.0', $result[0]);
        $this->assertEquals('192.168.1.255', $result[1]);
    }

    public function testCidrToRangeWithDifferentSubnet()
    {
        $result = $this->network->cidrToRange('10.0.0.0/8');
        
        $this->assertEquals('10.0.0.0', $result[0]);
        $this->assertEquals('10.255.255.255', $result[1]);
    }

    public function testIpInRange()
    {
        $result = $this->network->ipInRange('192.168.1.50', '192.168.1.0', '192.168.1.255');
        
        $this->assertTrue($result);
    }

    public function testIpInRangeOutside()
    {
        $result = $this->network->ipInRange('192.168.2.50', '192.168.1.0', '192.168.1.255');
        
        $this->assertFalse($result);
    }

    public function testIpInRangeWithInvalidIp()
    {
        $result = $this->network->ipInRange('invalid', '192.168.1.0', '192.168.1.255');
        
        $this->assertNull($result);
    }

    public function testGetUserIP()
    {
        // Mock $_SERVER
        $_SERVER['REMOTE_ADDR'] = '192.168.1.100';
        unset($_SERVER['HTTP_X_FORWARDED_FOR']);
        
        $result = $this->network->getUserIP();
        
        $this->assertEquals('192.168.1.100', $result);
    }

    public function testGetUserIPWithProxy()
    {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '203.0.113.1';
        $_SERVER['REMOTE_ADDR'] = '192.168.1.100';
        
        $result = $this->network->getUserIP();
        
        $this->assertEquals('203.0.113.1', $result);
    }

    public function testGetUserIPAsArray(): void
    {
        $_SERVER['REMOTE_ADDR'] = '192.168.1.100';
        unset($_SERVER['HTTP_X_FORWARDED_FOR']);

        $result = $this->network->getUserIP(null, true);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('type', $result);
        $this->assertArrayHasKey('userip', $result);
        $this->assertEquals('direct', $result['type']);
    }

    public function testGetUserIPThrowsWhenDieIfEmptyAndNoIp(): void
    {
        unset($_SERVER['REMOTE_ADDR']);
        unset($_SERVER['HTTP_X_FORWARDED_FOR']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('getUserIP');
        $this->network->getUserIP(null, false, true);
    }

    public function testGetServerIP()
    {
        $_SERVER['SERVER_ADDR'] = '192.168.1.1';
        
        $result = $this->network->getServerIP();
        
        $this->assertEquals('192.168.1.1', $result);
    }

    public function testGetServerIPAsArray()
    {
        $_SERVER['SERVER_ADDR'] = '192.168.1.1';
        
        $result = $this->network->getServerIP(true);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('type', $result);
        $this->assertArrayHasKey('serverip', $result);
        $this->assertEquals('server', $result['type']);
    }

    public function testUsesReverseProxy()
    {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '203.0.113.1';
        
        $result = $this->network->usesReverseProxy();
        
        $this->assertTrue($result);
    }

    public function testUsesReverseProxyWithSpecifiedProxy()
    {
        $_SERVER['REMOTE_ADDR'] = '192.168.1.100';
        unset($_SERVER['HTTP_X_FORWARDED_FOR']);
        
        $result = $this->network->usesReverseProxy('192.168.1.100');
        
        $this->assertTrue($result);
    }

    public function testUsesReverseProxyNoProxy()
    {
        $_SERVER['REMOTE_ADDR'] = '192.168.1.100';
        unset($_SERVER['HTTP_X_FORWARDED_FOR']);
        
        $result = $this->network->usesReverseProxy();
        
        $this->assertFalse($result);
    }

    protected function tearDown(): void
    {
        // Clean up $_SERVER
        unset($_SERVER['REMOTE_ADDR']);
        unset($_SERVER['HTTP_X_FORWARDED_FOR']);
        unset($_SERVER['SERVER_ADDR']);
    }
}

