<?php
// We'll place custom application helpers here.

use Classes\{Cookie, Database, Datetime, Session};
use Google\Cloud\Storage\StorageClient;

if(!function_exists('fetchSettings')){
    function fetchSettings($column){
        $db = Database::getInstance();
        return $db->selectQuery('__settings', ['referral_percentage', 'number_pcc', 'number_pvc', 'number_cpc', 'number_words_pcc', 'tester_earn', 'basic_earn', 'bronze_earn', 'silver_earn', 'emerald_earn', 'jasper_earn', 'ruby_earn', 'gold_earn'], ['WHERE' => ['id' => 1]])->results()[$column];
    }
}

if(!function_exists('creditAmount')){
    function creditAmount($plan){
        $credit_amount = ['tester' => fetchSettings('tester_earn'), 'basic' => fetchSettings('basic_earn'), 'bronze' => fetchSettings('bronze_earn'), 'silver' => fetchSettings('silver_earn'), 'emerald' => fetchSettings('emerald_earn'), 'jasper' => fetchSettings('jasper_earn'), 'ruby' => fetchSettings('ruby_earn'), 'gold' => fetchSettings('gold_earn')];
        if(array_key_exists($plan, $credit_amount)) return $credit_amount[$plan];
        return false;
    }
}

// This can be removed if added to the db, then user creditAmout and fetchSettings function to call it.
if(!function_exists('commissionAmount')){
    function commissionAmount($plan){
        $commission_amount = ['tester' => 200, 'basic' => 334, 'bronze' => 534, 'silver' => 867, 'emerald' => fetchSettings('emerald_earn'), 'jasper' => fetchSettings('jasper_earn'), 'ruby' => fetchSettings('ruby_earn'), 'gold' => fetchSettings('gold_earn')];
        if(array_key_exists($plan, $commission_amount)) return $commission_amount[$plan];
        return false;
    }
}

if(!function_exists('planAmount')){
    function planAmount($plan, bool $flip = false){
        $plans = ['tester' => 300000, 'basic' => 500000, 'bronze' => 800000, 'silver' => 1300000, 'emerald' => 2100000, 'jasper' => 3500000, 'ruby' => 5800000, 'gold' => 9500000];
        if($flip){
            $plans = array_flip($plans);
            if(array_key_exists($plan, $plans)) return $plans[$plan];
        }else{
            if(array_key_exists($plan, $plans)) return $plans[$plan];
        }
        return false;
    }
}

if(!function_exists('bonusPercentage')){
    function bonusPercentage(){
        return fetchSettings('referral_percentage')/100;
    }
}

// We'll use this function to get the user plan
if(!function_exists('getPlan')){
    function getPlan(int $userId){
        $db = Database::getInstance();
        return $db->selectQuery('__subscriptions', ['plan'], [
            'WHERE' => ['user_id' => $userId], 
            'ORDER' => ['id', 'DESC']])->results()['plan'];
    }
}

// We'll also check if you have active subscription
if(!function_exists('checkSubscription')){
    function checkSubscription(int $userId){
        $db = Database::getInstance();
        $subscription_data = $db->selectQuery('__subscriptions', ['subscription_end'], [
            'WHERE' => ['user_id' => $userId], 
            'ORDER' => ['id', 'DESC']])->results();
        if($subscription_data['subscription_end'] < Datetime::timestamp()) return false;
        return true;
    }
}

// we'll check if the user has earned that day, we'll use date of credit where not bonus to figure this out
if(!function_exists('checkEligiblity')){
    function checkEligiblity(int $userId, string $remark){
        $db = Database::getInstance();
        $today = Datetime::setDateTimeFormat('Y-m-j', Datetime::timestamp());
        $account_last_earn = $db->selectQuery('__accounts', ['date_added'], [
            'WHERE' => ['user_id' => $userId, 'remark' => $remark],
            'ORDER' => ['id', 'DESC']])->results()['date_added'];
        $last_earned_date = Datetime::setDateTimeFormat('Y-m-j', $account_last_earn);
        if($today === $last_earned_date) return false;
        return true;
    }
}

// Algorithm to store the user earn progress
if(!function_exists('addToAccount')){
    function addToAccount(int $userId, string $type, string $remark, int $amount, string $status){
        $db = Database::getInstance();
        $account_insert = $db->insertQuery('__accounts', [
            'user_id' => $userId,
            'type' => $type,
            'remark' => $remark,
            'amount' => $amount,
            'date_added' => Datetime::timestamp(),
            'status' => $status
        ]);
        return (!$account_insert->error()) ? true : false;
    }
}

// Algorithm to handle all crediting user account: on post creation
if(!function_exists('earnByPostCreation')){
    function earnByPostCreation(){
        if(Session::exists('uid')){
            // ''    check qualification - get how many posts the user should post to earn, store the progress in a cookie for that day.
            if(checkSubscription(Session::get('uid')) && checkEligiblity(Session::get('uid'), 'earned')){

                $cookie_name = '_pcc'.Session::get('uid');

                if(Cookie::exists($cookie_name)){

                    Cookie::def_set($cookie_name, Cookie::get($cookie_name)+1, 'tomorrow');

                    if(Cookie::get($cookie_name) >= fetchSettings('number_pcc')) addToAccount(Session::get('uid'), 'credit', 'earned', creditAmount(getPlan(Session::get('uid'))), 'paid');

                }else{
                    Cookie::def_set($cookie_name, 1, 'tomorrow');
                }
            }
        }
    }
}

// Algorithm to handle all crediting user account: on post view
if(!function_exists('earnByPostView')){
    function earnByPostView($store){
        if(Session::exists('uid')){
            // ''    check if the user is qualified to earn >= 30 views of [diff posts]
            if(checkSubscription(Session::get('uid')) && checkEligiblity(Session::get('uid'), 'earned')){

                $cookie_name = '_pvc'.Session::get('uid');

                if(Cookie::exists($cookie_name)){

                    $_pvc = explode(',', Cookie::get($cookie_name));

                    if(!in_array($store, $_pvc)){

                        Cookie::def_set($cookie_name, Cookie::get($cookie_name).','.$store, 'tomorrow');

                    }

                    $_pvc_recount = explode(',', Cookie::get($cookie_name));

                    $progress_count = count($_pvc_recount);

                    if($progress_count >= fetchSettings('number_pvc')) addToAccount(Session::get('uid'), 'credit', 'earned', creditAmount(getPlan(Session::get('uid'))), 'paid');

                }else{
                    Cookie::def_set($cookie_name, $store, 'tomorrow');
                }
            }
        }
    }
}

// Algorithm to handle all crediting user account: on comment post
if(!function_exists('earnByCommentPost')){
    function earnByCommentPost($store){
        if(Session::exists('uid')){
            // ''    check if the user is qualified to earn >= 6 comments of [diff posts]
            if(checkSubscription(Session::get('uid')) && checkEligiblity(Session::get('uid'), 'earned')){

                $cookie_name = '_cpc'.Session::get('uid');

                if(Cookie::exists($cookie_name)){

                    $_cpc = explode(',', Cookie::get($cookie_name));

                    if(!in_array($store, $_cpc)){

                        Cookie::def_set($cookie_name, Cookie::get($cookie_name).','.$store, 'tomorrow');

                    }

                    $_cpc_recount = explode(',', Cookie::get($cookie_name));

                    $progress_count = count($_cpc_recount);

                    if($progress_count >= fetchSettings('number_cpc')) addToAccount(Session::get('uid'), 'credit', 'earned', creditAmount(getPlan(Session::get('uid'))), 'paid');

                }else{
                    Cookie::def_set($cookie_name, $store, 'tomorrow');
                }
            }
        }
    }
}

if(!function_exists('upload_object')){
    function upload_object($bucketName, $objectName, $source){
        $storage = new StorageClient();
        $file = fopen($source, 'r');
        $bucket = $storage->bucket($bucketName);
        $object = $bucket->upload($file, [
            'name' => $objectName
        ]);
        // printf('Uploaded %s to gs://%s/%s' . PHP_EOL, basename($source), $bucketName, $objectName);
    }
}

if(!function_exists('delete_object')){
    function delete_object($bucketName, $objectName, $options = []){
        $storage = new StorageClient();
        $bucket = $storage->bucket($bucketName);
        $object = $bucket->object($objectName);
        $object->delete();
        // printf('Deleted gs://%s/%s' . PHP_EOL, $bucketName, $objectName);
    }
}

if(!function_exists('list_objects_with_prefix')){
    function list_objects_with_prefix($bucketName, $prefix){
        $objects_array = [];
        $storage = new StorageClient();
        $bucket = $storage->bucket($bucketName);
        $options = ['prefix' => $prefix];
        foreach($bucket->objects($options) as $object){
            $objects_array[] = $object->name();
            // printf('Object: %s' . PHP_EOL, $object->name());
        }
        return $objects_array;
    }
}
