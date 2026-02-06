<?php

declare(strict_types=1);

namespace PHPUtils;

/* ────────────────────────────────────────────────────────────────────────── */
/*                                  Crypto                                    */
/* ────────────────────────────────────────────────────────────────────────── */
/**
 * Crypto
 * 
 * A class to handle encryption and hashing
 * 
 * @package PHPUtils
 */
class Crypto extends Base {

    /**
     * Generates a random IV for the given encryption method (hex-encoded for storage).
     *
     * @param string $method The encryption method to use
     * @return string The generated IV as hex string
     */
    public function genIV(string $method): string {
        $len   = openssl_cipher_iv_length($method);
        $bytes = openssl_random_pseudo_bytes($len);
        return bin2hex($bytes);
    }

    /**
     * Encrypt a string using a password, and optionally an IV.
     * When $iv is true, a random IV is used and prepended to the ciphertext (base64(iv_raw . ciphertext)).
     *
     * @param string $str The string to encrypt
     * @param string $password The password to use
     * @param string $method The encryption method. Defaults to aes-256-cbc
     * @param bool $iv Whether to use a random IV (prepended to output). Defaults to false
     * @return string The encrypted string (when iv=true, base64 of IV + ciphertext)
     */
    public function encryptwithpw(string $str, string $password, string $method = 'aes-256-cbc', bool $iv = false): string {
        if ($iv) {
            $ivRaw = hex2bin($this->genIV($method));
            $encrypted = openssl_encrypt($str, $method, $password, OPENSSL_RAW_DATA, $ivRaw);
            return base64_encode($ivRaw . $encrypted);
        }
        return openssl_encrypt($str, $method, $password);
    }

    /**
     * Decrypt a string using a password, and optionally an IV.
     * When $iv is empty and the string was produced with iv=true, the embedded IV is used.
     * When $iv is non-empty, it is treated as hex and used as the IV (ciphertext only in $str).
     *
     * @param string $str The string to decrypt (base64 when IV was embedded, or raw/base64 ciphertext when $iv provided)
     * @param string $password The password to use
     * @param string $method The encryption method. Defaults to aes-256-cbc
     * @param string $iv Optional hex-encoded IV when not embedded. Defaults to ''
     * @return string|false The decrypted string or false on failure
     */
    public function decryptwithpw(string $str, string $password, string $method = 'aes-256-cbc', string $iv = ''): string|false {
        $ivLen = openssl_cipher_iv_length($method);
        if ($iv !== '') {
            $ivRaw = hex2bin($iv);
            $ciphertext = base64_decode($str, true) ?: $str;
            return openssl_decrypt($ciphertext, $method, $password, OPENSSL_RAW_DATA, $ivRaw);
        }
        $decoded = base64_decode($str, true);
        if ($decoded !== false && strlen($decoded) > $ivLen) {
            $ivRaw = substr($decoded, 0, $ivLen);
            $ciphertext = substr($decoded, $ivLen);
            return openssl_decrypt($ciphertext, $method, $password, OPENSSL_RAW_DATA, $ivRaw);
        }
        return openssl_decrypt($str, $method, $password);
    }

    /**
     * Hash a string using the given algorithm.
     *
     * @param string $str The string to hash
     * @param string $hash The hash algorithm. Defaults to sha512
     * @return string The hashed string
     */
    public function hash(string $str, string $hash = 'sha512'): string {
        return hash($hash, $str);
    }

    /**
     * Verify a string against a hash (timing-safe comparison).
     *
     * @param string $str The string to verify
     * @param string $hash The hash to verify against
     * @param string $hashmethod The hash method. Defaults to sha512
     * @return bool Whether the hash is valid
     */
    public function verifyhash(string $str, string $hash, string $hashmethod = 'sha512'): bool {
        return hash_equals($hash, hash($hashmethod, $str));
    }
}