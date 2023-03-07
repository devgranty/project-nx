<?php

/**
 * @author Grant Adiele <grantobioma@gmail.com>
 * 
 * @version 1.0.0
 * 
 * Handle cookie manipulation easily using this
 * Cookie class.
 * 
 */
namespace Classes;

class Cookie extends Datetime{

	private static $_secure_cookie = ENABLE_SECURE_COOKIE;

  	public static function set(string $name, string $value, int $expiry, string $path = '/', string $domain = '', bool $httponly = true){
		if(setcookie($name, $value, time()+$expiry, $path, $domain, self::$_secure_cookie, $httponly)){
			return true;
		}
		return false;
	}

	public static function def_set(string $name, string $value, string $expiry_date, string $path = '/', string $domain = '', bool $httponly = true){
		if(setcookie($name, $value, self::stringToTimestamp($expiry_date), $path, $domain, self::$_secure_cookie, $httponly)){
			return true;
		}
		return false;
	}

	public static function delete($name){
		self::set($name, '', 10);
	}

	public static function get($name){
		return $_COOKIE[$name];
	}

	public static function exists($name){
		return isset($_COOKIE[$name]);
	}
}
