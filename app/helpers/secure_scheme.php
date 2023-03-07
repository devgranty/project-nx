<?php
use Classes\{Router};

// Redirect to secure url, if enable secure scheme is true.
if(!function_exists('secureScheme')){
    function secureScheme(){
        if(@$_SERVER['HTTPS'] != "on" && ENABLE_SECURE_SCHEME){
            $redirect = "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            Router::redirect($redirect);
        }
    }
    
    secureScheme();
}