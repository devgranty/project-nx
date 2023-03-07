<?php

/**
 * 
 * Encryption class allows for encryption
 * and decryption of data.
 *
 * @version 1.3.4
 * 
 */
namespace Classes;

class Encryption{
    public function encrypt($string, $cipher = DEFAULT_CIPHER_METHOD, $encryption_key = DEFAULT_ENCRYPTION_KEY, $options = 0, $encryption_iv = DEFAULT_ENCRYPTION_IV){
        if(in_array(strtolower($cipher), openssl_get_cipher_methods())){
            return openssl_encrypt($string, $cipher, $encryption_key, $options, $encryption_iv);
        }else{
            return "Invalid cipher method passed: $cipher";
        }
    }

    public function decrypt($string, $cipher = DEFAULT_CIPHER_METHOD, $encryption_key = DEFAULT_ENCRYPTION_KEY, $options = 0, $encryption_iv = DEFAULT_ENCRYPTION_IV){
        if(in_array(strtolower($cipher), openssl_get_cipher_methods())){
            return openssl_decrypt($string, $cipher, $encryption_key, $options, $encryption_iv);
        }else{
            return "Invalid cipher method passed: $cipher";
        }
    }
}
