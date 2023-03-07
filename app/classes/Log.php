<?php

/**
 * @author Grant Adiele <grantobioma@gmail.com>
 * 
 * @version 1.0.0
 * 
 * Allows for easy logging of activities generated
 * by events.
 * 
 */
namespace Classes;

class Log extends Database{
    private $_activity = '';

    public static function getUserIP(){
        if(!empty($_SERVER['HTTP_CLIENT_IP'])){
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }else{
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    public function add(int $event_id, int $user_id, string $event, string $subject, string $remark = ''){
        $this->_activity = self::getInstance()->insertQuery('Logs', [
            'event_id' => $event_id,
            'user_id' => $user_id,
            'ip' => self::getUserIP(),
            'event' => $event,
            'subject' => $subject,
            'remark' => $remark,
            'date_added' => self::timestamp()
        ]);
        if(!$this->_activity->error()){
            return true;
        }else{
            return $this->_activity->error_info();
        }
    }
}
