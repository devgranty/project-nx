<?php

/**
 * @author Grant Adiele <grantobioma@gmail.com>
 * 
 * @version 1.0.0
 * 
 * Handle routing request such as redirects
 * and get url contents using this class.
 * 
 */
namespace Classes;

class Router{
	public static function redirect(string $url){
		if(!headers_sent()){
			header("Location:$url");
			exit();
		}else{
			echo "<meta http-equiv='refresh' content='0;url=$url'/>";
			exit();
		}
	}

	public static function get_file_contents_curl(string $url){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}

	public static function check_file_url(string $url, array $allowed_status_codes = [200]){
		if(filter_var($url, FILTER_VALIDATE_URL)){
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_NOBODY, true);
			curl_exec($ch);
			$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			if(in_array($status_code, $allowed_status_codes)){
				$status = true;
			}else{
				$status = false;
			}
			curl_close($ch);
		}else{
			$status = false;
		}
		return $status;
	}
}
