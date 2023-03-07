<?php

// Let us make sure you are running a compatiable PHP version.
if(!function_exists('versionCheck')){
	function versionCheck(){
		if(phpversion() < 7) return exit('You are running a lower version of PHP. PHP version 7 or higher is required to run this application. Your version: '.phpversion());
	}
	
	versionCheck();
}
