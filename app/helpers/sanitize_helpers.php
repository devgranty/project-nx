<?php

// Input sanitizers, sanitizes input types.
if(!function_exists('sanitize_input')){
	function sanitize_input($input, $trim = true){
		$output = htmlentities($input, ENT_QUOTES, 'UTF-8');
		return ($trim) ? trim($output) : $output;
	}
}

if(!function_exists('sanitize_email')){
	function sanitize_email($input){
		return filter_var($input, FILTER_SANITIZE_EMAIL);
	}
}

if(!function_exists('sanitize_int')){
	function sanitize_int($input){
		return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
	}
}
