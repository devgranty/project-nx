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

use Classes\{Cookie, Router};

if(!empty($_GET['action'])){
    if($_GET['action'] == 'delete' && Cookie::exists('_mah')){
        Cookie::delete('_mah');
    }
}

Router::redirect('signin.php');
