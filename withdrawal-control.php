<?php
/**
 * ---------------------------------------
 * LOAD UP APPLICATION
 * ---------------------------------------
 * Let us load up all helpers, classes
 * and application configuration.
 * Feels good to take a nap now!
 */
require_once __DIR__.'/bootstrap/app.php';

use Classes\{Database, Hash, Router, Session};

$db = Database::getInstance();
$session = Session::startSession();

// If user session doesn't exist, then redirect to sign in.
if(!Session::exists('uid')) Router::redirect('signin.php');
// Check if user is an editor/moderator
if(Session::get('rank') == 'editor' || Session::get('rank') == 'moderator') Router::redirect('403.php');

if(isset($_POST['create_csv'])){
	if(Hash::compareHashes(Hash::hashUseHmac('sha256', get_page_name(), Session::get('_token')), $_POST['form_token'])){
		$users_debit_request = [];
		foreach($_POST as $key => $value){
			if($key == 'create_csv'){
				continue;
			}
			$id = explode('_', $key);
			$id = end($id);
			$users_debit_request[] = $db->query('SELECT __users.email, __users.account_number, __users.bank, __accounts.amount FROM __users JOIN __accounts ON __users.id = __accounts.user_id WHERE __accounts.type = ? AND __accounts.user_id = ? ORDER BY __accounts.id DESC', ['debit', $id])->results();
		}

		$filename = 'bankdetails.csv';
		header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename=$filename");
		$output = fopen("php://output", "w");
		// $csv_header = array_keys($users_debit_request[0]);
		$csv_header = ['Email Address', 'Account Number', 'Slug', 'Transfer Amount'];
		fputcsv($output, $csv_header);
		foreach($users_debit_request as $row){
			fputcsv($output, $row);
		}
		fclose($output);
	}else{
		Router::redirect('withdrawal-list.php?msg=invalid_token');
	}
}


if(isset($_POST['mark_paid'])){
	if(Hash::compareHashes(Hash::hashUseHmac('sha256', get_page_name(), Session::get('_token')), $_POST['form_token'])){
		foreach($_POST as $key => $value){
			if($key == 'mark_paid'){
				continue;
			}
			$id = explode('_', $key);
			$id = next($id);
			$db->updateQuery('__accounts', ['status' => 'paid'], ['id' => $id]);
		}
		Router::redirect('withdrawal-list.php');
	}else{
		Router::redirect('withdrawal-list.php?msg=invalid_token');
	}
}

if(isset($_POST['mark_unpaid'])){
	if(Hash::compareHashes(Hash::hashUseHmac('sha256', get_page_name(), Session::get('_token')), $_POST['form_token'])){
		foreach($_POST as $key => $value){
			if($key == 'mark_unpaid'){
				continue;
			}
			$id = explode('_', $key);
			$id = next($id);
			$db->updateQuery('__accounts', ['status' => 'unpaid'], ['id' => $id]);
		}
		Router::redirect('withdrawal-list.php');
	}else{
		Router::redirect('withdrawal-list.php?msg=invalid_token');
	}
}

if(isset($_POST['mark_pending'])){
	if(Hash::compareHashes(Hash::hashUseHmac('sha256', get_page_name(), Session::get('_token')), $_POST['form_token'])){
		foreach($_POST as $key => $value){
			if($key == 'mark_pending'){
				continue;
			}
			$id = explode('_', $key);
			$id = next($id);
			$db->updateQuery('__accounts', ['status' => 'pending'], ['id' => $id]);
		}
		Router::redirect('withdrawal-list.php');
	}else{
		Router::redirect('withdrawal-list.php?msg=invalid_token');
	}
}
