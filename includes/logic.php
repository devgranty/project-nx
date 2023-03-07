<?php

use Classes\{Database, Datetime, Router, Session};

$db = Database::getInstance();

// Init variables
$suspended_user_data_array = $referred_user_data_array = [];

// Background jobs
if(Session::exists('rank')){
    if(Session::get('rank') != 'editor'){
        // Resume all suspended accounts if suspension limit expires
        $unix_now = Datetime::timestamp();
        $suspended_user = $db->query('SELECT id, suspend_expiry FROM __users WHERE status = ? OR status = ?', ['suspend 1 week', 'suspend 1 month']);
        while($suspended_user_data = $suspended_user->results()) $suspended_user_data_array[] = $suspended_user_data;
        foreach($suspended_user_data_array as $suspended_user_data){
            if($unix_now >= $suspended_user_data['suspend_expiry']){
                $db->updateQuery('__users', ['status' => 'active', 'suspend_expiry' => ''], ['id' => $suspended_user_data['id']]);
            }
        }
    }
}

if(Session::exists('uid')){
    // Fetch required user data
    $user_data = $db->selectQuery('__users', ['referral_code', 'signup_step'], ['WHERE' => ['id' => Session::get('uid')]])->results();

    // Get last signup step
    switch($user_data['signup_step']){
        case 2:
            if(get_page_name() != 'verify') Router::redirect('verify.php');
        break;
        case 3:
            if(get_page_name() != 'subscribe') Router::redirect('subscribe.php');
        break;
    }

    // Fetch referral count
    $user_referral_count = $db->selectQuery('__users', ['referrer'], ['WHERE' => ['referrer' => $user_data['referral_code']]])->row_count();
    $db->updateQuery('__users', ['referral_count' => $user_referral_count], ['id' => Session::get('uid')]);

    // Fetch all referred
    $referred_users = $db->selectQuery('__users', ['id'], ['WHERE' => ['referrer' => $user_data['referral_code']]]);
    while($referred_user_data = $referred_users->results()) $referred_user_data_array[] = $referred_user_data;
    foreach($referred_user_data_array as $referred_user_data){
        // Get user referral plan
        $user_referral_plan = $db->selectQuery('__subscriptions', ['plan'], ['WHERE' => ['user_id' => $referred_user_data['id'], 'first_subscription' => 1]])->results()['plan'];
        // Add new referral bonus
        if(!empty($user_referral_plan)){
            if(addToAccount(Session::get('uid'), 'credit', 'bonus', ((planAmount($user_referral_plan)/100)*bonusPercentage()), 'paid')){
                // Update subscription, set first subscription to 0, if it was added to the referral bonus
                $db->updateQuery('__subscriptions', ['first_subscription' => 0], ['user_id' => $referred_user_data['id'], 'first_subscription' => 1]);
            }
        }
    }
    
    // Fetch sum of credits
    (int)$_CREDIT = $db->query('SELECT SUM(amount) AS credit FROM __accounts WHERE user_id = ? AND type = ? AND remark = ? AND status = ?', [Session::get('uid'), 'credit', 'earned', 'paid'])->results()['credit'];

    // Fetch sum of bonus
    (int)$_BONUS = $db->query('SELECT SUM(amount) AS bonus FROM __accounts WHERE user_id = ? AND type = ? AND remark = ? AND status = ?', [Session::get('uid'), 'credit', 'bonus', 'paid'])->results()['bonus'];

    // Fetch sum of debits
    (int)$_DEBIT = $db->query('SELECT SUM(amount) AS total_debit FROM __accounts WHERE user_id = ? AND type = ? AND status = ?', [Session::get('uid'), 'debit', 'paid'])->results()['total_debit'];

    // Calculate total balance
    (int)$_TOTAL_BALANCE = ($_CREDIT + $_BONUS) - $_DEBIT;
    if(($_CREDIT - $_DEBIT) < 0){
        $_BALANCE = 0;
        $_REMAINDER = $_CREDIT - $_DEBIT;
    }else{
        $_BALANCE = $_CREDIT - $_DEBIT;
        $_REMAINDER = 0;
    }
    $_BONUS = $_BONUS + ($_REMAINDER);

    // Update user balance
    $db->updateQuery('__users', ['total_balance' => $_TOTAL_BALANCE], ['id' => Session::get('uid')]);
}
