<?php

/**
 * 
 * @author Grant Adiele <grantobioma@gmail.com>
 * 
 * @version 1.0.0
 * 
 * Handle session manipulation easily using this
 * Session class.
 * 
 */
namespace Classes;

class Session{
	private static $_instance = null;

	private static $_session_started = false;

	private $_session_name = DEFAULT_SESSION_COOKIE_NAME;

	public function __construct(int $lifetime = 0, string $path = '/', string $domain = '', bool $secure = ENABLE_SECURE_SESSION_COOKIE, bool $httponly = true){
		session_name($this->_session_name);
		session_set_cookie_params($lifetime, $path, $domain, $secure, $httponly);
		self::$_session_started = session_start();
	}

	public static function startSession(){
		if(!isset(self::$_instance)){
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public static function regenerateId(bool $delete_old_session = false){
		if(self::$_session_started){
			return session_regenerate_id($delete_old_session);
		}else{
			return exit("Cannot regenerate session ID when session is not active");
		}
	}

	public static function set(string $name, string $value): string{
		return $_SESSION[$name] = $value;
	}

	public static function exists(string $name): bool{
		return (isset($_SESSION[$name])) ? true : false;
	}

	public static function get(string $name): string{
		return $_SESSION[$name];
	}

	public static function remove(string $name){
		unset($_SESSION[$name]);
	}
	
	public static function delete(){
		session_unset();
		session_destroy();
	}
}
