<?php
// Helper functions used in entire application.
use Classes\{Router};

if(!function_exists('dnd')){
	function dnd($data){
		echo '<pre>';
		print_r($data);
		echo '</pre>';
		exit();
	}
}

function alert_messages(array $_messages){
    if(is_array($_messages)){
        if(!empty($_messages)){
            for($i=0; $i < count($_messages); $i++){ 
                foreach($_messages[$i] as $type => $message){
                    switch($type){
                        case 'success':
							$alert = "
							<div class='alert alert-success alert-dismissible fade show' role='alert'>
								<i class='fas fa-check-circle' aria-hidden='true'></i>
								<button class='close' data-dismiss='alert' aria-lable='close'><span aria-hidden='true'>&times;</span></button>
								$message
							</div>";
                        break;
                        case 'info':
							$alert = "
							<div class='alert alert-info alert-dismissible fade show' role='alert'>
								<i class='fas fa-info-circle' aria-hidden='true'></i>
								<button class='close' data-dismiss='alert' aria-lable='close'><span aria-hidden='true'>&times;</span></button>
								$message
							</div>";
                        break;
                        case 'warning':
							$alert = "
							<div class='alert alert-warning alert-dismissible fade show' role='alert'>
								<i class='fas fa-exclamation-circle' aria-hidden='true'></i>
								<button class='close' data-dismiss='alert' aria-lable='close'><span aria-hidden='true'>&times;</span></button>
								$message
							</div>";
                        break;
                        case 'danger':
							$alert = "
							<div class='alert alert-danger alert-dismissible fade show' role='alert'>
								<i class='fas fa-exclamation' aria-hidden='true'></i>
								<button class='close' data-dismiss='alert' aria-lable='close'><span aria-hidden='true'>&times;</span></button>
								$message
							</div>";
						break;
						case 'primary':
							$alert = "
							<div class='alert alert-primary alert-dismissible fade show' role='alert'>
								<i class='fas fa-info-circle' aria-hidden='true'></i>
								<button class='close' data-dismiss='alert' aria-lable='close'><span aria-hidden='true'>&times;</span></button>
								$message
							</div>";
						break;
						case 'secondary':
							$alert = "
							<div class='alert alert-secondary alert-dismissible fade show' role='alert'>
								<i class='fas fa-info-circle' aria-hidden='true'></i>
								<button class='close' data-dismiss='alert' aria-lable='close'><span aria-hidden='true'>&times;</span></button>
								$message
							</div>";
						break;
						case 'light':
							$alert = "
							<div class='alert alert-light alert-dismissible fade show' role='alert'>
								<i class='fas fa-info-circle' aria-hidden='true'></i>
								<button class='close' data-dismiss='alert' aria-lable='close'><span aria-hidden='true'>&times;</span></button>
								$message
							</div>";
						break;
						case 'dark':
							$alert = "
							<div class='alert alert-dark alert-dismissible fade show' role='alert'>
								<i class='fas fa-info-circle' aria-hidden='true'></i>
								<button class='close' data-dismiss='alert' aria-lable='close'><span aria-hidden='true'>&times;</span></button>
								$message
							</div>";
                        break;
                        default:
							$alert = "
							<div class='alert alert-info alert-dismissible fade in' role='alert'>
								<i class='fas fa-info-circle' aria-hidden='true'></i>
								<button class='close' data-dismiss='alert' aria-lable='close'><span aria-hidden='true'>&times;</span></button>
								$message
							</div>";
                        break;
                    }
                    echo $alert;
                }
            }
        }
    }
}

if(!function_exists('post_values')){
	function post_values(array $values = [], string $submit_name){
		if(isset($_POST[$submit_name])){
			return $values = $_POST;
		}else{
			return $values;
		}
	}
}

if(!function_exists('get_page_name')){
	function get_page_name(){
		$page = $_SERVER['PHP_SELF'];
		$page = explode('/', $page);
		return str_replace('.php', '', end($page));
	}
}
