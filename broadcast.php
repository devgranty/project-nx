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

/**
 * ---------------------------------------
 * REGISTER OUR AUTOLOAD
 * ---------------------------------------
 * Composer provides a simple way to autoload
 * our vendor classes, that way we dont have to 
 * maunually require any class in our application
 * Oh, what a relief
 */
require_once __DIR__.'/vendor/autoload.php';

use Classes\{Database, Datetime, Hash, Router, Session};

$db = Database::getInstance();
$session = Session::startSession();
$purifier = new HTMLPurifier();

// Init variables
$_messages = $selected_users_array = [];

// If user session doesn't exist, then redirect to sign in.
if(!Session::exists('uid')) Router::redirect('signin.php');
// Check if user is an editor/moderator
if(Session::get('rank') == 'editor' || Session::get('rank') == 'moderator') Router::redirect('403.php');

if(isset($_POST['send_broadcast_to_selected'])){
	if(Hash::compareHashes(Hash::hashUseHmac('sha256', get_page_name(), Session::get('_token')), $_POST['form_token'])){
		$users = [];
		$excluded_users = [''];
		foreach($_POST as $key => $value){
            if($key == 'send_broadcast_to_selected' || $key == 'form_token'){
				continue;
			}
			$id = explode('_', $key);
            $users[] = next($id);
        }
	}else{
		Router::redirect('user-list.php?msg=invalid_token');
	}
}
if(isset($_POST['send_broadcast_to_all'])){
    if(Hash::compareHashes(Hash::hashUseHmac('sha256', get_page_name(), Session::get('_token')), $_POST['form_token'])){
		$users = [];
		$excluded_users = [''];
        $all_users = $db->selectQuery('__users', ['id']);
        while($user = $all_users->results()['id']){
            $users[] = $user;
        }
    }else{
		Router::redirect('user-list.php?msg=invalid_token');
	}
}
if(isset($_POST['send_broadcast_to_except'])){
    if(Hash::compareHashes(Hash::hashUseHmac('sha256', get_page_name(), Session::get('_token')), $_POST['form_token'])){
		$users = [];
		$excluded_users = [];
		$all_users = $db->selectQuery('__users', ['id']);
        while($user = $all_users->results()['id']){
            $users[] = $user;
        }
		foreach($_POST as $key => $value){
            if($key == 'send_broadcast_to_except' || $key == 'form_token'){
				continue;
			}
			$id = explode('_', $key);
            $excluded_users[] = next($id);
		}
    }else{
		Router::redirect('user-list.php?msg=invalid_token');
	}
}

if(!empty($users)) Session::set('broadcast_users', implode(',', $users));
if(!empty($excluded_users)) Session::set('broadcast_excluded_users', implode(',', $excluded_users));
if(!Session::exists('broadcast_users')) Router::redirect('user-list.php?msg=no_selected_user');
if(!Session::exists('broadcast_excluded_users')) Router::redirect('user-list.php?msg=no_selected_user');

foreach(explode(',', Session::get('broadcast_users')) as $key => $user_id){
	if(in_array($user_id, explode(',', Session::get('broadcast_excluded_users')))){
		continue;
	}
	$selected_users_array[] = $user_id;
}
$selected_users = implode(',', $selected_users_array);

$selected_user_data = $db->selectQuery('__users', ['uname', 'photo'], ['WHERE' => ['id' => $selected_users_array[array_rand($selected_users_array, 1)]]])->results();
if(!empty($selected_user_data['photo'])){
	$photo = IMG_CDN_URL.'photos/'.$selected_user_data['photo'];
}else{
	$photo = IMG_CDN_URL.'default/default-profile-picture-1280x1280.png';
}

if(isset($_POST['send_broadcast'])){
    if(Hash::compareHashes(Hash::hashUseHmac('sha256', get_page_name(), Session::get('_token')), $_POST['form_token'])){
		$allow_reply = isset($_POST['allow_reply']) ? 1 : 0;
		$broadcast = $db->insertQuery('__broadcasts', [
			'users' => $selected_users,
			'broadcast' => $purifier->purify($_POST['broadcast']),
			'allow_reply' => $allow_reply,
			'expiry' => Datetime::stringToTimestamp('+ 1 day')]);
        if(!$broadcast->error()){
            Router::redirect('user-list.php?msg=broadcast_successful');
        }else{
            Router::redirect('user-list.php?msg=broadcast_unsuccessful&error='.urlencode($broadcast->error_info()[2].":"));
        }
    }else{
        Router::redirect('user-list.php?msg=invalid_token');
    }
}

$post = post_values(['broadcast' => ''], 'send_broadcast');
?>

<!DOCTYPE html>
<html lang="en" dir="ltr" prefix="website: http://ogp.me/ns/website#">
	<head>
		<?php include_once __DIR__.'/includes/gtm.php'; ?>
		<title>Broadcast &#8208; <?=SITE_NAME?></title>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="theme-color" content="#000000">
		<link rel="icon" href="<?=SROOT?>favicon.png" sizes="19x19" type="image/png">
		<link rel="apple-touch-icon" href="<?=SROOT?>assets/icons/icon.png" type="image/png">
		<link rel="stylesheet" type="text/css" href="<?=SROOT?>assets/css/style.css?v=20200407">
		<link rel="stylesheet" type="text/css" href="<?=SROOT?>assets/quill-1.3.6/css/quill.snow.css">
		<meta name="robots" content="noindex, nofollow">
	</head>
	<body>
		<?php include_once __DIR__.'/includes/gtm-ns.php'; ?>
		<div class="min-vh-100">
			<?php include_once __DIR__.'/includes/nav.php'; 
				alert_messages($_messages);
			?>
			
			<div class="container mt-5">
				<div class="col-12 col-sm-10 offset-sm-1">
					<h2 class="text-center s-blue">Broadcast</h2>

					<div class="card text-white bg-dark mb-1">
						<div class="card-header">Sending broadcast to...</div>
						<div class="card-body">
							<div class="d-inline-flex">
								<img src="<?=$photo?>" width="25" height="25" class="rounded-circle s-pp-border-2x">
								<p class="card-text p-1 ml-1">
									<?php if(count($selected_users_array) == 1): ?>
										<span><?=$selected_user_data['uname']?></span>
									<?php else: ?>
										<span><?=$selected_user_data['uname']?> <span class="font-italic">+<?=(count($selected_users_array)-1)?> other person(s)</span></span>
									<?php endif; ?>
								</p>
							</div>
						</div>
					</div>

					<form role="form" action="" method="post" enctype="multipart/form-data" id="createForm">
						<div class="form-group">
							<div id="editor" style="height:250px;"><?=$post['broadcast']?></div>
						</div>
                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" name="allow_reply" class="form-check-input"/>
                                <label class="form-check-label" for="allow user to reply to this broadcast">Allow user(s) to reply to this broadcast</label>
                            </div>
                        </div>
						<input type="hidden" name="broadcast" value="" id="article"/>
						<input type="hidden" name="form_token" value="<?=Hash::hashUseHmac('sha256', get_page_name(), Session::get('_token'))?>"/>
						<div class="form-row">
							<div class="form-group">
								<button type="submit" name="send_broadcast" onsubmit="this.setAttribute('disabled', 'disabled');" class="btn btn-success">Send broadcast</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>

		<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
		<script src="https://unpkg.com/popper.js@1.12.6/dist/umd/popper.js" integrity="sha384-fA23ZRQ3G/J53mElWqVJEGJzU0sTs+SvzG8fXVWP+kJQ1lwFAOkcUOysnlKJC33U" crossorigin="anonymous"></script>
		<script type="text/javascript" src="<?=SROOT?>assets/bootstrap-material-design-4.1.1-dist/js/bootstrap-material-design.min.js"></script>
		<script type="text/javascript" src="<?=SROOT?>assets/quill-1.3.6/js/quill.min.js"></script>
		<script type="text/javascript" src="<?=SROOT?>assets/js/script.js?v=20200414"></script>
		<script type="text/javascript">
			var toolbarOptions = [['bold', 'italic', 'underline', 'strike'], ['link', 'blockquote', 'code-block'], [{ 'list': 'ordered'}, { 'list': 'bullet' }], [{ 'header': [1, 2, 3, 4, 5, 6, false] }], ['clean'], ['image'], [{'indent': '-1'}, {'indent': '+1'}]];
			var options = {
				debug: false,
				modules: {
					toolbar: {
						container: toolbarOptions,
						handlers: {
							image: imageHandler
						}
					},
				},
				placeholder: 'Type here...',
				theme: 'snow'
			};
			var quill = new Quill('#editor', options);
			// console.log('logging: ', quill);
			var form = document.querySelector('#createForm');
			form.onsubmit = function(){
				var article = document.querySelector('#article');
				article.value = quill.root.innerHTML;
				if(quill.root.innerText.length <= 1){
					alert("Editor cannot be empty");
					return false;
				}
			}
			function imageHandler(){
				var range = this.quill.getSelection();
				var value = prompt("Enter image url:", "http://");
				if(value){
					this.quill.insertEmbed(range.index, 'image', value, Quill.sources.USER);
				}
			}
		</script>
	</body>
</html>