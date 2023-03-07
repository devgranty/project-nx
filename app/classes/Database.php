<?php

/**
 * @author Grant Adiele <grantobioma@gmail.com>
 * 
 * @version 1.0.0
 * 
 * Database class provides properties and methods
 * which enables us perform operations on the DB.
 *
 */

namespace Classes;
use \PDO;
use \PDOException;

class Database extends Datetime{

	private static $_instance = null;

	private $_pdo, $_query, $_result, $_error = false;
	
	public function __construct(){
		try{
			$this->_pdo = new PDO(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD);
			// $this->_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // $this->_pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            // $this->_pdo->setAttribute(PDO::ATTR_PERSISTENT, true);
		}catch(PDOException $e){
			exit($e->getMessage());
		}
	}


	// Get instance of Database
	public static function getInstance(){
		if(!isset(self::$_instance)){
			self::$_instance = new self();
		}
		return self::$_instance;
	}


	/**
	 * Query method takes an sql statement with binding parameters to execute 
	 * the statement.
	 *
	 * ----------------------------------------------------------------------
	 * @param String
	 * @param Array
	 *
	 * ----------------------------------------------------------------------
	 * $sql = "SELECT * FROM users WHERE id = ?";
	 * query($sql, ['15']);
	 *
	 */
	public function query($sql, $values = []){
		$this->_error = false;
		if($this->_query = $this->_pdo->prepare($sql)){
			if(is_array($values)){
				$x = 1;
				foreach($values as $key => $value){
					$this->_query->bindValue($x, $value);
					$x++;
				}
			}else{
				return "Array required on parameter 2.";
			}
		}
		if($this->_query->execute()){
			$this;
		}else{
			$this->_error = true;
		}
		return $this;
	}

	
	/**
	 * Performs simple selecct queries on DB, allowing addition of conditions
	 * e.g. WHERE, ORDER & LIMIT clauses.
	 *
	 * ----------------------------------------------------------------------
	 * @param String
	 * @param Array
	 * @param Array
	 *
	 * ----------------------------------------------------------------------
	 * selectQuery('users', ['fname', 'email'], [
	 * 'WHERE' => ['id'=>'1'],
	 * 'ORDER' => ['id', 'fname', 'DESC'],
	 * 'LIMIT' => ['0', '10']]);
	 *
	 */
	public function selectQuery($table, $columns = [], $conditions = []){
		$columnString = $whereString = $orderString = $limitString = '';
		$values = [];
		if(is_array($columns)){
			foreach($columns as $column => $value){
				$columnString .= "$value, ";
			}
			$columnString = rtrim($columnString, ', ');
		}else{
			return "Array required on parameter 2.";
		}
		if(is_array($conditions)){
			if(array_key_exists('WHERE', $conditions)){
				$whereString = 'WHERE ';
				foreach($conditions['WHERE'] as $key => $value){
					$whereString .= "$key = ? AND ";
					$values[] = $value;
				}
				$whereString = rtrim($whereString, ' AND ');
			}
			if(array_key_exists('ORDER', $conditions)){
				$orderString = 'ORDER BY ';
				foreach($conditions['ORDER'] as $key => $value){
					$orderString .= "$value, ";
				}
				$orderString = rtrim($orderString, ', ');
				$lowerOrderString = strtolower($orderString);
				$orderArray = explode(', ', $lowerOrderString);
				if(in_array('desc', $orderArray) || in_array('asc', $orderArray)){
					$str_pos = strrpos($orderString, ',');
					$orderString = substr_replace($orderString, '', $str_pos, 1);
				}
			}
			if(array_key_exists('LIMIT', $conditions)){
				$limitString = 'LIMIT ';
				foreach($conditions['LIMIT'] as $key => $value){
					$limitString .= "$value, ";
				}
				$limitString = rtrim($limitString, ', ');
			}
		}else{
			return "Array required on parameter 3.";
		}
		$sql = trim("SELECT {$columnString} FROM {$table} {$whereString} {$orderString} {$limitString}");
		if($this->query($sql, $values)){
			$this;
		}else{
			$this->_error = true;
		}
		return $this;
	}


	/**
	 * Allows running of simple insert queries on database.
	 *
	 * ----------------------------------------------------------------------
	 * @param String
	 * @param Array
	 *
	 * ----------------------------------------------------------------------
	 * insertQuery('users', [
	 * 'username' => 'Username',
	 * 'email' => 'user@email.com',
	 * 'fname' => 'First Name']);
	 *
	 */
	public function insertQuery($table, $fields = []){
		$this->_error = false;
		$columnString = $bindString = '';
		$values = [];
		if(is_array($fields)){
			foreach($fields as $column => $value){
				$columnString .= "$column, ";
				$bindString .= "?, ";
				$values[] = $value;
			}
			$columnString = rtrim($columnString, ', ');
			$bindString = rtrim($bindString, ', ');
		}else{
			return "Array required on parameter 2.";
		}
		$sql = "INSERT INTO {$table} ({$columnString}) VALUES ({$bindString})";
		if($this->query($sql, $values)){
			$this;
		}else{
			$this->_error = true;
		}
		return $this;
	}


	/**
	 * Allows running of simple update queries on database.
	 *
	 * ----------------------------------------------------------------------
	 * @param String
	 * @param Array
	 * @param Array
	 *
	 * ----------------------------------------------------------------------
	 * updateQuery('users', [
	 * 'username' => 'Username',
	 * 'email' => 'user@email.com',
	 * 'fname' => 'First Name'],
	 * ['id' => '20']);
	 *
	 */
	public function updateQuery($table, $fields = [], $conditions = []){
		$this->_error = false;
		$fieldString = $conditionString = '';
		$values = [];
		if(is_array($fields)){
			foreach($fields as $field => $value){
				$fieldString .= "$field = ?, ";
				$values[] = $value;
			}
			$fieldString = rtrim($fieldString, ', ');
		}else{
			return "Array required on parameter 2.";
		}
		if(is_array($conditions)){
			foreach($conditions as $condition => $value){
				$conditionString .= "$condition = ? AND ";
				$values[] = $value;
			}
			$conditionString = rtrim($conditionString, ' AND ');
		}else{
			return "Array required on parameter 3.";
		}
		$sql = trim("UPDATE {$table} SET {$fieldString} WHERE {$conditionString}");
		if($this->query($sql, $values)){
			$this;
		}else{
			$this->_error = true;
		}
		return $this;
	}


	/**
	 * Allows running of simple delete queries on database.
	 *
	 * ----------------------------------------------------------------------
	 * @param String
	 * @param Array
	 *
	 * ----------------------------------------------------------------------
	 *
	 * deleteQuery('users', ['id' => '2']);
	 *
	 */
	public function deleteQuery($table, $conditions = []){
		$this->_error = false;
		$conditionString = '';
		$values = [];
		if(is_array($conditions)){
			foreach($conditions as $condition => $value){
				$conditionString .= "$condition = ? AND ";
				$values[] = $value;
			}
			$conditionString = rtrim($conditionString, ' AND ');
		}else{
			return "Array required on parameter 2.";
		}
		$sql = trim("DELETE FROM {$table} WHERE {$conditionString}");
		if($this->query($sql, $values)){
			$this;
		}else{
			$this->_error = true;
		}
		return $this;
	}

	// Retrieve number of affected rows by a query.
	public function row_count(){
		return $this->_query->rowCount();
	}

	// Retrieve columns affected by a query.
	public function fetch_column(){
		return $this->_query->fetchColumn();
	}

	// Get last insert id of a update or insert query.
	public function last_insert_id(){
		return $this->_pdo->lastInsertId();
	}

	// Get a single result from a query.
	public function results(){
		return $this->_result = $this->_query->fetch(PDO::FETCH_ASSOC);
	}

	// Fetch all results of a query.
	public function allResults(){
		return $this->_result = $this->_query->fetchAll(PDO::FETCH_ASSOC);
	}

	// Returns true if an error occurs in a DB query.
	public function error(){
		return $this->_error;
	}

	// Returns detailed info about error in a query.
	public function error_info(){
		return $this->_query->errorinfo();
	}
}

