<?php

/**
 * 
 * @author Grant Adiele <grantobioma@gmail.com>
 * 
 * @version 1.3.4
 * 
 * Str class provides a truly random cryptographically
 * generated strings and integer methods.
 * 
 */
namespace Classes;

class Str{
    public static function randomInt(int $length = 1){
        if($length <= 18){
            return random_int(10**($length - 1), (10**$length)-1);
        }else{
            return "Maximum length is 18, $length given.";
        }
    }
    
    public static function randomStr(int $length = 16){
        $string = '';
        while (($len = strlen($string)) < $length) {
            $size = $length - $len;
            $bytes = random_bytes($size);
            $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
        }
        return $string;
    }

    public static function createSlug(string $title){
        $slug_arr = [];
        $allowed_chars_arr = str_split('abcdefghijklmnopqrstuvwxyz0123456789 ');
        $title = strtolower($title);
        $title_arr = str_split($title);
        foreach($title_arr as $title_chars){
            if(in_array($title_chars, $allowed_chars_arr)) $slug_arr[] = $title_chars;
        }
        $slug = implode('', $slug_arr);
        $slug = trim($slug);
        return str_replace(' ', '-', $slug);
    }
}
