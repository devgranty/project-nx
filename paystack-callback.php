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

use Classes\{Database, Datetime, Router, Session};

$db = Database::getInstance();
$session = Session::startSession();

// If user session doesn't exist, then redirect to login.
if(!Session::exists('uid')) Router::redirect('login.php');

$user_signup_step = $db->selectQuery('__users', ['signup_step'], ['WHERE' => ['id' => Session::get('uid')]])->results()['signup_step'];

$curl = curl_init();

$reference = !empty($_GET['reference']) ? $_GET['reference'] : '';
if(!$reference){
    exit('No reference supplied');
}

curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.paystack.co/transaction/verify/".rawurlencode($reference),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "accept: application/json",
        "authorization: Bearer ".PAYSTACK_API_KEY,
        "cache-control: no-cache"
    ],
]);
$response = curl_exec($curl);

$_err = curl_error($curl);

if($_err){
    exit('Curl returned error: '.$_err);
}

$tranx = json_decode($response);

if(!$tranx->status){
    exit('API returned error: '.$tranx['message']);
}

if($tranx->data->status == 'success'){
    $first_subscription = ($user_signup_step == 3) ? 1 : 0;
    $amount = $tranx->data->amount;
    switch($amount){
        case planAmount('tester'):
            $plan = 'tester';
        break;
        case planAmount('basic'):
            $plan = 'basic';
        break;
        case planAmount('bronze'):
            $plan = 'bronze';
        break;
        case planAmount('silver'):
            $plan = 'silver';
        break;
        case planAmount('emerald'):
            $plan = 'emerald';
        break;
        case planAmount('jasper'):
            $plan = 'jasper';
        break;
        case planAmount('ruby'):
            $plan = 'ruby';
        break;
        case planAmount('gold'):
            $plan = 'gold';
        break;
    }
    $insertQuery = $db->insertQuery('__subscriptions', [
        'user_id' => Session::get('uid'),
        'reference' => $reference,
        'plan' => $plan,
        'subscription_date' => Datetime::timestamp(),
        'subscription_end' => Datetime::convertToDateFormat('Y-m-j H:i:s', Datetime::stringToTime('+ 30 days')),
        'first_subscription' => $first_subscription
    ]);
    if(!$insertQuery->error() && $user_signup_step == 3){
        $db->updateQuery('__users', [
            'signup_step' => 4
        ], ['id' => Session::get('uid')]);
    }
    Router::redirect('dashboard.php?msg=activated');
}
