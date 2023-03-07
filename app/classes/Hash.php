<?php

/**
 * 
 * @author Grant Adiele <grantobioma@gmail.com>
 * 
 * @version 1.6.5
 * 
 * Create hashes using hash algos, compare hashes 
 * using time safe string comparison.
 * 
 */
namespace Classes;

class Hash{

    public function compareHashes(string $known_string, string $user_string){
		return hash_equals($known_string, $user_string);
	}
	
	public static function hashData(string $algo, string $data, bool $raw_output = false){
		if(in_array($algo, hash_algos())){
			return hash($algo, $data, $raw_output);
		}else{
			return exit("Unknown hash algorithm passed as argument");
		}
	}

	public static function hashUseHmac(string $algo, string $data, string $key, bool $raw_output = false){
		if(in_array($algo, hash_hmac_algos())){
			return hash_hmac($algo, $data, $key, $raw_output);
		}else{
			return exit("Unknown hash hmac algorithm passed as argument");
		}
	}
}
