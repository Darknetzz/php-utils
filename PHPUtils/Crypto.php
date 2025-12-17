<?php

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
     * Generates a random IV for the given encryption method
     *
     * @param mixed $method The encryption method to use
     * @return string The generated IV
     */
    function genIV(string $method) {
        $len   = openssl_cipher_iv_length($method);
        $bytes = openssl_random_pseudo_bytes($len);
        return bin2hex($bytes);
    }

    /**
     * Encrypt a string using a password, and optionally an IV
     *
     * @param  mixed $str The string to encrypt
     * @param  mixed $password The password to use
     * @param  mixed $method The encryption method to use. Defaults to aes-256-cbc
     * @param  mixed $iv Whether to use an IV or not. Defaults to false
     * @return string The encrypted string
     */
     function encryptwithpw(string $str, string $password, string $method = 'aes-256-cbc', bool $iv = false) {
        $iv = ($iv ? $this->genIV($method) : '');
        return openssl_encrypt($str,$method,$password,iv:$iv);
    }


    /**
     * Decrypt a string using a password, and optionally an IV
     *
     * @param  mixed $str The string to decrypt
     * @param  mixed $password The password to use
     * @param  mixed $method The encryption method to use. Defaults to aes-256-cbc
     * @param  mixed $iv Whether to use an IV or not. Defaults to ''
     * @return string The decrypted string
     */
    function decryptwithpw(string $str, string $password, string $method = 'aes-256-cbc', string $iv = '') {
        return openssl_decrypt($str,$method,$password,iv:$iv);
    }

    /**
     * hash
     *
     * @param  mixed $str The string to hash
     * @param  mixed $hash The hash method to use. Defaults to sha512
     * @return string The hashed string
     */
    function hash(string $str, string $hash = 'sha512') {
        return hash($str, $hash);
    }

    /**
     * verifyhash
     * 
     * Verifies a hash
     * 
     * @param  mixed $str The string to verify
     * @param  mixed $hash The hash to verify against
     * @param  mixed $hashmethod The hash method to use. Defaults to sha512
     * 
     * @return bool Whether the hash is valid or not
     */
    function verifyhash(string $str, string $hash, string $hashmethod = 'sha512') {
        return hash($hashmethod, $str) == $hash;
    }
}

?>