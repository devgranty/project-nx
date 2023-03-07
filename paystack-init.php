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

use Classes\{Router, Session, Validate};

$session = Session::startSession();

// If user session doesn't exist, then redirect to login.
if(!Session::exists('uid')) Router::redirect('login.php');

$validator = new Validate();

$curl = curl_init();

if(isset($_POST['amount']) && isset($_POST['email_prepared_for_paystack']) && $validator->validateEmail($_POST['email_prepared_for_paystack']) && $validator->validateInt($_POST['amount'])){

  $amount = $_POST['amount'];
  $email = $_POST['email_prepared_for_paystack'];

  $callback_url = SITE_URL.SROOT.'paystack-callback.php';
 
  curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.paystack.co/transaction/initialize",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => json_encode([
      'amount' => $amount,
      'email' => $email,
      'callback_url' => $callback_url
    ]),
    CURLOPT_HTTPHEADER => [
      "authorization: Bearer ".PAYSTACK_API_KEY,
      "content-type: application/json",
      "cache-control: no-cache"
    ],
  ]);
  $response = curl_exec($curl);

  $_err = curl_error($curl);

  if($_err){
    exit('Curl returned error: '.$_err);
  }

  $tranx = json_decode($response, true);

  if(!$tranx['status']){
    exit('API returned error: '.$tranx['message']);
  }
  
  // Redirect to payment page so that user can pay
  Router::redirect($tranx['data']['authorization_url']);
}
