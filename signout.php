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

use Classes\{Cookie, Session, Router};

$session = Session::startSession();

if(Session::exists('uid')) Session::delete();

// We need to redirect the user from the page they are coming from
if(!empty($_GET['next'])){
    Router::redirect($_GET['next']);
}else{
    Router::redirect('signin.php');
}
