<?php

/**
 * @author Grant Adiele <grantobioma@gmail.com>
 * 
 * @version 1.0.0
 * 
 * Class Datetime handles date and time manipulation
 * from setting timezone, converting time and setting time.
 *
 */
namespace Classes;

class Datetime{
	
	protected function __construct(){
		date_default_timezone_set(SET_TIMEZONE);
	}

	public static function timestamp(){
		new self();
		return time();
	}

	public static function timeTranslate($unix_timestamp, bool $short_form = false){
		$current_time = self::timestamp();
		$time_difference = $current_time - $unix_timestamp;
		$seconds = $time_difference;
		$minutes = round($seconds / 60);
		$hours = round($seconds / 3600);
		$days = round($seconds / 86400);
		$weeks = round($seconds / 604800);
		$months = round($seconds / 2629440);
		$years = round($seconds / 31553280);
		if($seconds <= 60){
			return $short_form ? $seconds."s" : "Just now";
		}elseif($minutes <= 60){
			if($minutes == 1){
				return $short_form ? "1m" : "One minute ago";
			}else{
				return $short_form ? $minutes."min" : "$minutes minutes ago";
			}
		}elseif($hours <= 24){
			if($hours == 1){
				return $short_form ? "1h" : "One hour ago";
			}else{
				return $short_form ? $hours."h" : "$hours hours ago";
			}
		}elseif($days <= 7){
			if($days == 1){
				return $short_form ? "1d" : "Yesterday";
			}else{
				return $short_form ? $days."d" : "$days days ago";
			}
		}elseif($weeks <= 4.3){
			if($weeks == 1){
				return $short_form ? "1w" : "One week ago";
			}else{
				return $short_form ? $weeks."w" : "$weeks weeks ago";
			}
		}elseif($months <= 12){
			if($months == 1){
				return $short_form ? "1mo" : "One month ago";
			}else{
				return $short_form ? $months."mo" : "$months months ago";
			}
		}else{
			if($years == 1){
				return $short_form ? "1y" : "One year ago";
			}else{
				return $short_form ? $years."y" : "$years years ago";
			}
		}
	}

	public static function setDateTime($unix_timestamp){
		new self();
		return date('j M Y, H:i:s', $unix_timestamp);
	}

	public static function setDateTimeFormat(string $format, $unix_timestamp){
		new self();
		return date($format, $unix_timestamp);
	}

	public static function stringToTimestamp(string $strtime){
		return strtotime($strtime, self::timestamp());
	}
}
