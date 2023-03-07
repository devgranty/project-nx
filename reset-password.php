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

use Classes\{Crypt, Database, Datetime, Router, Session, Validate};

$db = Database::getInstance();
$crypt = new Crypt();
$session = Session::startSession();

// Init variable
$_messages = [];
$_err = 0;

// If user session still exists, then redirect to dashboard.
if(Session::exists('uid')) Router::redirect('dashboard.php');

if(!empty($_GET['euid']) && !empty($_GET['expid']) && Validate::validateInt($_GET['expid'])){

	$user_id = sanitize_int($crypt->decrypt($_GET['euid']));
	$expiry_id = sanitize_int($_GET['expid']);
	
	$selectQuery = $db->selectQuery('__users', ['reset_psd_expiry'], [
		'WHERE' => ['id' => $user_id, 'reset_psd_expiry' => $expiry_id]
	]);

	if($selectQuery->row_count() > 0){
		
		$data = $selectQuery->results();

		if($data['reset_psd_expiry'] > Datetime::timestamp()){
			if(isset($_POST['update_password'])){
				if(!Validate::comparePasswords($_POST['password'], $_POST['confirm_password'])){
					$_messages[] = ["warning" => "Passwords do not match."];
					$_err = 1;
				}
				if(!$_err){
					$updateQuery = $db->updateQuery('__users', [
						'psd' => password_hash($_POST['password'], PASSWORD_DEFAULT), 
						'reset_psd_expiry' => ''], ['id' => $user_id]);
					if(!$updateQuery->error()){
						Router::redirect('signin.php?msg=psd_reset');
					}else{
						$_messages[] = ["danger" => $updateQuery->error_info()[2].": Failed to update password."];
					}
				}
			}
		}else{
			Router::redirect('signin.php');	
		}
	}else{
		Router::redirect('signin.php');	
	}
}else{
	Router::redirect('signin.php');
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr" prefix="website: http://ogp.me/ns/website#">
	<head>
		<?php include_once __DIR__.'/includes/gtm.php'; ?>
		<title>Reset password &#8208; <?=SITE_NAME?></title>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="theme-color" content="#000000">
		<link rel="icon" href="<?=SROOT?>favicon.png" sizes="19x19" type="image/png">
		<link rel="apple-touch-icon" href="<?=SROOT?>assets/icons/icon.png" type="image/png">
		<link rel="stylesheet" type="text/css" href="<?=SROOT?>assets/css/style.css?v=20200407">
		<meta name="robots" content="noindex, nofollow">
	</head>
	<body>
		<?php include_once __DIR__.'/includes/gtm-ns.php'; ?>
		<div class="min-vh-100">
			<?php include_once __DIR__.'/includes/nav.php';
				alert_messages($_messages);
			?>

			<div class="container mt-5">
				<div class="col-12 col-md-6 offset-md-3">
					<h2 class="text-center s-blue">Update your password</h2>
					<form role="form" action="" method="post" enctype="multipart/form-data">
						<div class="form-group">
							<label class="sr-only" for="password">New password</label>
							<input type="password" name="password" required placeholder="New password" class="form-control" minlength="6"/>
						</div>
						<div class="form-group">
							<label class="sr-only" for="password">Confirm new password</label>
							<input type="password" name="confirm_password" required placeholder="Confirm new password" class="form-control" minlength="6"/>
						</div>
						<div class="form-group">
							<button type="submit" name="update_password" class="btn btn-primary btn-block">Get it done!</button>
						</div>
						<div class="form-group text-right">
							<a href="<?=SROOT?>signin.php" class="text-primary">Go back to sign in</a>
						</div>
					</form>
				</div>
			</div>
		</div>

		<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
		<script src="https://unpkg.com/popper.js@1.12.6/dist/umd/popper.js" integrity="sha384-fA23ZRQ3G/J53mElWqVJEGJzU0sTs+SvzG8fXVWP+kJQ1lwFAOkcUOysnlKJC33U" crossorigin="anonymous"></script>
		<script type="text/javascript" src="<?=SROOT?>assets/bootstrap-material-design-4.1.1-dist/js/bootstrap-material-design.min.js"></script>
		<script type="text/javascript" src="<?=SROOT?>assets/js/script.js?v=20200414"></script>

	</body>
</html>