<?php

/**
 * @author Grant Adiele <grantobioma@gmail.com>
 * 
 * @version 1.3.4
 * 
 * Crypt class allows for encryption
 * and decryption of data.
 * 
 */
namespace Classes;

class Crypt{

    public function encrypt(string $string, string $cipher = DEFAULT_CIPHER_METHOD, string $crypt_key = DEFAULT_CRYPT_KEY, int $options = 0, string $crypt_iv = DEFAULT_CRYPT_IV){
        if(in_array(strtolower($cipher), openssl_get_cipher_methods())){
            return openssl_encrypt($string, $cipher, $crypt_key, $options, $crypt_iv);
        }else{
            return "Invalid cipher method passed: $cipher";
        }
    }

    public function decrypt(string $string, string $cipher = DEFAULT_CIPHER_METHOD, string $crypt_key = DEFAULT_CRYPT_KEY, int $options = 0, string $crypt_iv = DEFAULT_CRYPT_IV){
        if(in_array(strtolower($cipher), openssl_get_cipher_methods())){
            return openssl_decrypt($string, $cipher, $crypt_key, $options, $crypt_iv);
        }else{
            return "Invalid cipher method passed: $cipher";
        }
    }
}
