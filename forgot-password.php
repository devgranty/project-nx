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

use Classes\{Cookie, Crypt, Database, Datetime, Router, Session, Validate};
use PHPMailer\PHPMailer\PHPMailer;

$db = Database::getInstance();
$crypt = new Crypt();
$mail = new PHPMailer();
$session = Session::startSession();

// Init variables
$_messages = [];
$_err = 0;
$disable_btn = '';

// If user session still exists, then redirect to dashboard.
if(Session::exists('uid')) Router::redirect('dashboard.php');

if(isset($_POST['confirm'])){
	if(!Validate::validateEmail($_POST['email'])){
		$_messages[] = ["warning" => "Invalid email address."];
		$_err = 1;
	}
	if(!$_err){
		$selectQuery = $db->selectQuery('__users', ['id', 'uname', 'email'], [
			'WHERE' => ['email' => sanitize_email($_POST['email'])]
		]);
	
		if($selectQuery->row_count() > 0){
			$data = $selectQuery->results();
	
			$encrypt_user_id = $crypt->encrypt($data['id']);
			$expiry_id = Datetime::stringToTimestamp('+ 24 hours');
			$password_reset_url = SITE_URL.SROOT."reset-password.php?euid=$encrypt_user_id&expid=$expiry_id";

			$mail->isSMTP();
			$mail->Host = MAIL_DEFAULT_HOST;
			$mail->Port = MAIL_DEFAULT_PORT;
			$mail->SMTPSecure = MAIL_DEFAULT_SMTPSECURE;
			$mail->SMTPAuth = MAIL_DEFAULT_SMTPAUTH;
			$mail->Username = MAIL_DEFAULT_USERNAME;
			$mail->Password = MAIL_DEFAULT_PASSWORD;
			$mail->CharSet = 'UTF-8';
			$mail->setFrom(MAIL_DEFAULT_FROM, MAIL_DEFAULT_FROM_NAME);
			$mail->addReplyTo(MAIL_DEFAULT_FROM, MAIL_DEFAULT_FROM_NAME);
			$mail->addAddress($_POST['email']);
			$mail->isHTML(true);
			$mail->Subject = "Reset your ".SITE_NAME." password";
			$mail->Body = "<html><body><div><p>Hi $data[uname], We got a request to reset your ".SITE_NAME." password. To complete this action click this link:</p>
			<p><a href='$password_reset_url' style='color:#337ab7;'>$password_reset_url</a></p>
			<p>If the above link doesn't work, copy and paste the URL in a new browser window. The URL will expire in 24 hours for security reasons. If this request is not from you, simply ignore this mail.</p>
			<div style='margin-top:15px;'>Thanks,</div>
			<div>The ".SITE_NAME." Team</div></div></body></html>";

			if($mail->send()){
				$updateQuery = $db->updateQuery('__users', ['reset_psd_expiry' => $expiry_id], [
					'id' => $data['id']
				]);
				if(!$updateQuery->error()){
					$_messages[] = ["success" => "Password reset was successful, kindly check your email at $data[email] to complete password reset. Can't find email? - do check in your spam folder."];

					$expiry = 60*5;
					Cookie::set('_fpexp', 'true', $expiry);
					// Disable the button initially
					$disable_btn = 'disabled="disabled"';
				}else{
					$_messages[] = ["danger" => $updateQuery->error_info()[2].": Password reset unsuccessful."];
				}
			}else{
				$_messages[] = ["warning" => "Failed to send email, password reset unsuccessful."];
			}
		}else{
			$_messages[] = ["warning" => "This email address does not exist."];
		}
	}
}

if(Cookie::exists('_fpexp')){
	$disable_btn = 'disabled="disabled"';
}
$post = post_values(['email' => ''], 'confirm');
?>

<!DOCTYPE html>
<html lang="en" dir="ltr" prefix="website: http://ogp.me/ns/website#">
	<head>
		<?php include_once __DIR__.'/includes/gtm.php'; ?>
		<title>Forgot password &#8208; <?=SITE_NAME?></title>
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
					<h2 class="text-center s-blue">Forgot password</h2>
					<form role="form" action="" method="post" enctype="multipart/form-data">
						<div class="form-group">
							<label class="sr-only" for="username">Email address</label>
							<input type="email" name="email" value="<?=$post['email']?>" required placeholder="Email address" class="form-control" minlength="5" maxlength="100"/>
						</div>
						<div class="form-group">
							<button type="submit" name="confirm" <?=$disable_btn?> class="btn btn-primary btn-block">That's it!</button>
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