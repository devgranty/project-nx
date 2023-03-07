<?php

/**
 * 
 * @author Grant Adiele <grantobioma@gmail.com>
 * 
 * @version 1.0.0
 * 
 * Class Validate validates different inputs.
 * 
 */
namespace Classes;

class Validate extends Database{
	private static $_query, $_count = 0;

	public function comparePasswords($password, $confirm_password){
		if($password === $confirm_password) return true;
		return false;
	}

	public static function checkDuplicates($value, $table, $column){
		self::$_query = self::getInstance()->selectQuery($table, [$column], [
			'WHERE' => [$column=>$value]]);
		self::$_count = self::$_query->row_count();
		return self::$_count;
	}

	public static function validateEmail($input){
		if(filter_var($input, FILTER_VALIDATE_EMAIL)) return true;
		return false;
	}

	public static function validateInt($input){
		if(filter_var($input, FILTER_VALIDATE_INT) === 0 || !filter_var($input, FILTER_VALIDATE_INT) === false) return true;
		return false;
	}

	public static function validateFloat($input){
		if(filter_var($input, FILTER_VALIDATE_FLOAT) === 0 || !filter_var($input, FILTER_VALIDATE_FLOAT) === false) return true;
		return false;
	}

	public static function validateUrl($input){
		if(filter_var($input, FILTER_VALIDATE_URL)) return true;
		return false;
	}
}
