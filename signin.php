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

use Classes\{Cookie, Crypt, Database, Datetime, Router, Session, Str};

$db = Database::getInstance();
$crypt = new Crypt();
$session = Session::startSession();

// Init variables
$_messages = [];
$_allowed = $enable_sign_in_by_auth = 0;

// If user session still exists, then redirect to dashboard.
if(Session::exists('uid')) Router::redirect('dashboard.php');

if(Cookie::exists('_mah')){
	// decrypt the _mah cookie
	$decrypted_auth_hash = $crypt->decrypt(Cookie::get('_mah'));

	// check if the cookie is associated to any user on the db and retrieve results
	$user_data = $db->selectQuery('__users', ['id', 'uname', 'rank', 'status', 'suspend_expiry'], ['WHERE' => ['auth_hash' => $decrypted_auth_hash]]);
	if($user_data->row_count() == 1){

		$user_data_results = $user_data->results();

		$enable_sign_in_by_auth = 1;

		if(isset($_POST['sign_in_by_auth'])){
			switch($user_data_results['status']){
				case 'active':
					$_allowed = 1;
				break;
				case 'suspend 1 week':
					$_messages[] = ["info" => "This account has been suspended for a time period of 1 week and will resume on ".Datetime::setDateTime($data['suspend_expiry']).". <a href='".SROOT."learn-more.php?q=account-suspension' class='alert-link' target='_blank'>Learn more</a>"];
					$_allowed = 0;
				break;
				case 'suspend 1 month':
					$_messages[] = ["info" => "This account has been suspended for a time period of 1 month and will resume on ".Datetime::setDateTime($data['suspend_expiry']).". <a href='".SROOT."learn-more.php?q=account-suspension' class='alert-link' target='_blank'>Learn more</a>"];
					$_allowed = 0;
				break;
				case 'deleted':
					$_messages[] = ["info" => "This is an invalid account. <a href='".SROOT."learn-more.php?q=account-suspension' class='alert-link' target='_blank'>Learn more</a>"];
					$_allowed = 0;
				break;
			}

			if($_allowed){
				Session::regenerateId();
				Session::set('uid', $user_data_results['id']);
				Session::set('uname', $user_data_results['uname']);
				Session::set('rank', $user_data_results['rank']);
				Session::set('_token', bin2hex(random_bytes(32)));
				// We need to redirect the user from the page they are coming from
				if(!empty($_GET['next'])){
					Router::redirect($_GET['next']);
				}else{
					Router::redirect('dashboard.php');
				}
			}
		}
	}
}

if(isset($_POST['sign_in_by_psd'])){
	$selectQuery = $db->query('SELECT id, uname, rank, psd, status, suspend_expiry FROM __users WHERE uname = ? OR email = ? OR phone = ?', [$_POST['username_email_phone'], $_POST['username_email_phone'], $_POST['username_email_phone']]);

	if($selectQuery->row_count() > 0){
		$data = $selectQuery->results();
		if(password_verify($_POST['password'], $data['psd'])){

			switch($data['status']){
				case 'active':
					$_allowed = 1;
				break;
				case 'suspend 1 week':
					$_messages[] = ["info" => "This account has been suspended for a time period of 1 week and will resume on ".Datetime::setDateTime($data['suspend_expiry']).". <a href='".SROOT."learn-more.php?q=account-suspension' class='alert-link' target='_blank'>Learn more</a>"];
					$_allowed = 0;
				break;
				case 'suspend 1 month':
					$_messages[] = ["info" => "This account has been suspended for a time period of 1 month and will resume on ".Datetime::setDateTime($data['suspend_expiry']).". <a href='".SROOT."learn-more.php?q=account-suspension' class='alert-link' target='_blank'>Learn more</a>"];
					$_allowed = 0;
				break;
				case 'deleted':
					$_messages[] = ["info" => "This is an invalid account. <a href='".SROOT."learn-more.php?q=account-suspension' class='alert-link' target='_blank'>Learn more</a>"];
					$_allowed = 0;
				break;
			}

			if($_allowed){
				// Remember me
				if(isset($_POST['remember_me'])){
					// generate a hash
					$auth_hash = Str::randomStr(200);

					// encrypt hash and store as cookie
					$encrypted_auth_hash = $crypt->encrypt($auth_hash);
					$expiry = 60*60*24*90;
					Cookie::set('_mah', $encrypted_auth_hash, $expiry);

					// store hash in db
					$db->updateQuery('__users', ['auth_hash' => $auth_hash], ['id' => $data['id']]);
				}
				Session::regenerateId();
				Session::set('uid', $data['id']);
				Session::set('uname', $data['uname']);
				Session::set('rank', $data['rank']);
				Session::set('_token', bin2hex(random_bytes(32)));
				// We need to redirect the user from the page they are coming from
				if(!empty($_GET['next'])){
					Router::redirect($_GET['next']);
				}else{
					Router::redirect('dashboard.php');
				}
			}
		}else{
			$_messages[] = ["warning" => "Incorrect username or password."];
		}
	}else{
		$_messages[] = ["warning" => "Incorrect username or password."];
	}
}

if(!empty($_GET['msg'])){
	switch($_GET['msg']){
		case 'psd_reset':
			$_messages[] = ["success" => "Your password has been updated successfully."];
		break;
	}
}

$post = post_values(['username_email_phone' => '', 'password' => ''], 'sign_in_by_psd');
?>

<!DOCTYPE html>
<html lang="en" dir="ltr" prefix="website: http://ogp.me/ns/website#">
	<head>
		<?php include_once __DIR__.'/includes/gtm.php'; ?>
		<title>Sign in &#8208; <?=SITE_NAME?></title>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="theme-color" content="#000000">
		<link rel="icon" href="<?=SROOT?>favicon.png" sizes="19x19" type="image/png">
		<link rel="apple-touch-icon" href="<?=SROOT?>assets/icons/icon.png" type="image/png">
		<link rel="stylesheet" type="text/css" href="<?=SROOT?>assets/css/style.css?v=20200407">
		<meta name="robots" content="index">
		<meta name="description" content="Sign in into your <?=SITE_NAME?> account to get started">
		<link rel="canonical" href="<?=SITE_URL.SROOT.get_page_name().'.php'?>">
		<meta property="og:title" content="Sign in &#8208; <?=SITE_NAME?>">
		<meta property="og:type" content="website">
		<meta property="og:image" content="<?=SITE_URL.SROOT?>assets/icons/icon.png">
		<meta property="og:image:type" content="image/png">
		<meta property="og:image:width" content="146">
		<meta property="og:image:height" content="146">
		<meta property="og:url" content="<?=SITE_URL.SROOT.get_page_name().'.php'?>">
		<meta property="og:description" content="Sign in into your <?=SITE_NAME?> account to get started">
		<meta property="og:locale" content="en_US">
		<meta property="og:site_name" content="<?=SITE_NAME?>">
		<meta name="twitter:card" content="summary">
		<meta name="twitter:site" content="">
		<meta name="twitter:title" content="Sign in &#8208; <?=SITE_NAME?>">
		<meta name="twitter:description" content="Sign in into your <?=SITE_NAME?> account to get started">
		<meta name="twitter:image" content="<?=SITE_URL.SROOT?>assets/icons/icon.png">
	</head>
	<body>
		<?php include_once __DIR__.'/includes/gtm-ns.php'; ?>
		<div class="min-vh-100">
			<?php include_once __DIR__.'/includes/nav.php'; 
				alert_messages($_messages);
			?>

			<div class="container mt-5">

				<div class="col-12 col-md-6 offset-md-3">

					<?php if(!$enable_sign_in_by_auth): ?>

						<h2 class="text-center s-blue">Sign in</h2>
						<p class="text-muted text-center">Using your <?=SITE_NAME?> account</p>
						<form role="form" action="" method="post" enctype="multipart/form-data">
							<div class="form-group">
								<label class="sr-only" for="username, email, phone number">Username, email address or phone number</label>
								<input type="text" name="username_email_phone" value="<?=$post['username_email_phone']?>" required placeholder="Username, email address or phone number" class="form-control" minlength="2" maxlength="100"/>
							</div>
							<div class="form-group">
								<label class="sr-only" for="password">Password</label>
								<input type="password" name="password" value="<?=$post['password']?>" required placeholder="Password" class="form-control" minlength="6"/>
							</div>
							<div class="form-group">
								<div class="form-check">
									<input type="checkbox" name="remember_me" class="form-check-input"/>
									<label class="form-check-label" for="keep me signed in on this device">Keep me signed in on this device</label>
								</div>
							</div>
							<div class="form-group">
								<button type="submit" name="sign_in_by_psd" class="btn btn-primary btn-block">Let me in!</button>
							</div>
							<div class="form-group text-center">
								<a href="<?=SROOT?>signup.php" class="text-primary">Don't have an account? Sign up</a>
							</div>
							<div class="form-group text-center">
								<a href="<?=SROOT?>forgot-password.php" class="text-primary">Forgot Password?</a>
							</div>
						</form>

					<?php else: ?>

						<div class="card">
							<div class="card-header bg-transparent border-success">
								<h2 class="text-center s-blue">Continue to your <?=SITE_NAME?> account</h2>
								<p class="text-center text-muted">Click continue to sign in</p>
							</div>

							<div class="card-body border-bottom">
								<h5 class="card-title text-center text-primary"><?=$user_data_results['uname']?></h5>
								<form role="form" action="" method="post" enctype="multipart/form-data">
									<div class="form-row">
										<div class="form-group col-6">
											<button type="submit" name="sign_in_by_auth" class="btn btn-success btn-block"><i class="fas fa-check" aria-hidden="true"></i> Continue</button>
										</div>
										<div class="form-group col-6">
											<a href="<?=SROOT?>remove-account.php?action=delete" class="btn btn-danger btn-block"><i class="fas fa-times" aria-hidden="true"></i> Remove</a>
										</div>
									</div>
								</form>
							</div>
							<!-- <div class="card-footer border-top-0">
								<a href="<?=SROOT?>signin.php?disable_auth_sign_in=1" class="btn btn-primary btn-block"><i class="fas fa-plus" aria-hidden="true"></i> Add account</a>
							</div> -->
						</div>

					<?php endif; ?>

				</div>
			</div>
		</div>

		<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
		<script src="https://unpkg.com/popper.js@1.12.6/dist/umd/popper.js" integrity="sha384-fA23ZRQ3G/J53mElWqVJEGJzU0sTs+SvzG8fXVWP+kJQ1lwFAOkcUOysnlKJC33U" crossorigin="anonymous"></script>
		<script type="text/javascript" src="<?=SROOT?>assets/bootstrap-material-design-4.1.1-dist/js/bootstrap-material-design.min.js"></script>
		<script type="text/javascript" src="<?=SROOT?>assets/js/script.js?v=20200414"></script>

	</body>
</html>