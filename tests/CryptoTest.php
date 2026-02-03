<?php

declare(strict_types=1);

namespace PHPUtils\Tests;

use PHPUnit\Framework\TestCase;
use PHPUtils\Crypto;

/**
 * Test class for Crypto
 */
class CryptoTest extends TestCase
{
    private Crypto $crypto;

    protected function setUp(): void
    {
        $this->crypto = new Crypto();
    }

    public function testHashReturnsCorrectAlgorithmOrder(): void
    {
        $str = 'password123';
        $result = $this->crypto->hash($str, 'sha256');
        $this->assertIsString($result);
        $this->assertEquals(64, strlen($result));
        $this->assertEquals(hash('sha256', $str), $result);
    }

    public function testHashDefaultSha512(): void
    {
        $str = 'test';
        $result = $this->crypto->hash($str);
        $this->assertEquals(hash('sha512', $str), $result);
    }

    public function testVerifyhashWithValidHash(): void
    {
        $str = 'secret';
        $hash = $this->crypto->hash($str);
        $this->assertTrue($this->crypto->verifyhash($str, $hash));
    }

    public function testVerifyhashWithInvalidHash(): void
    {
        $this->assertFalse($this->crypto->verifyhash('secret', 'wronghash'));
    }

    public function testGenIVReturnsHexString(): void
    {
        $iv = $this->crypto->genIV('aes-256-cbc');
        $this->assertIsString($iv);
        $this->assertMatchesRegularExpression('/^[a-f0-9]+$/i', $iv);
        $this->assertEquals(32, strlen($iv)); // 16 bytes = 32 hex chars for aes-256-cbc
    }

    public function testEncryptDecryptWithoutIv(): void
    {
        $plain = 'hello world';
        $password = 'secretkey';
        $encrypted = $this->crypto->encryptwithpw($plain, $password);
        $this->assertIsString($encrypted);
        $decrypted = $this->crypto->decryptwithpw($encrypted, $password);
        $this->assertEquals($plain, $decrypted);
    }

    public function testEncryptDecryptWithIv(): void
    {
        $plain = 'hello world';
        $password = 'secretkey';
        $encrypted = $this->crypto->encryptwithpw($plain, $password, 'aes-256-cbc', true);
        $this->assertIsString($encrypted);
        $decrypted = $this->crypto->decryptwithpw($encrypted, $password);
        $this->assertEquals($plain, $decrypted);
    }
}
