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

use Classes\{Cookie, Database, Hash, Router, Session, Str, Validate};
use PHPMailer\PHPMailer\PHPMailer;

$db = Database::getInstance();
$mail = new PHPMailer();
$session = Session::startSession();

// Init variables
$_messages = [];
$_err = 0;
$disable_btn = '';

// If user session doesn't exist, then redirect to sign in.
if(!Session::exists('uid')) Router::redirect('signin.php');

// Require logic.
require_once __DIR__.'/includes/logic.php';

$user_data = $db->selectQuery('__users', ['email', 'verification_code'], ['WHERE' => ['id' => Session::get('uid')]])->results();

// Redirect user if account is verified
if(empty($user_data['verification_code'])) Router::redirect('dashboard.php?rdr=1&msg='.get_page_name());

if(isset($_POST['verify'])){
	if(Hash::compareHashes(Hash::hashUseHmac('sha256', get_page_name(), Session::get('_token')), $_POST['form_token'])){
		if(!Validate::validateInt($_POST['verification_code'])){
			$_messages[] = ["warning" => "The code you entered is invalid"];
			$_err = 1;
		}
		if(!$_err){
			$verification_code = sanitize_int($_POST['verification_code']);
			if($verification_code === $user_data['verification_code']){

				$signup_step = (Session::get('rank') !== 'editor') ? 4 : 3;
				
				$updateQuery = $db->updateQuery('__users', [
					'verification_code' => '',
					'signup_step' => $signup_step], ['id' => Session::get('uid')]);
				if(!$updateQuery->error()){
					if(Session::get('rank') !== 'editor'){
						Router::redirect('dashboard.php?msg=verified');
					}else{
						Router::redirect('subscribe.php?msg=verified');
					}
				}else{
					$_messages[] = ["danger" => $insertQuery->error_info()[2].": Unable to verify your account."];
				}
			}else{
				$_messages[] = ["warning" => "Incorrect verification code entered."];
			}
		}
	}else{
		$_messages[] = ["warning" => "Invalid token"];
	}
}


if(isset($_POST['resend'])){
	if(Hash::compareHashes(Hash::hashUseHmac('sha256', get_page_name(), Session::get('_token')), $_POST['form_token'])){
		if(!Validate::validateEmail($_POST['email'])){
			$_messages[] = ["warning" => "The email you entered is invalid"];
			$_err = 1;
		}
		if(Validate::checkDuplicates($_POST['email'], '__users', 'email') > 0 && $_POST['email'] !== $user_data['email']){
			$_messages[] = ["warning" => "The email you entered already exists"];
			$_err = 1;
		}
		if(!$_err){
			$gen_random_int = Str::randomInt(5);

			$email = sanitize_email($_POST['email']);

			$updateQuery = $db->updateQuery('__users', ['email' => $email, 'verification_code' => $gen_random_int], ['id' => Session::get('uid')]);
			if(!$updateQuery->error()){
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
				$mail->addAddress($email);
				$mail->isHTML(true);
				$mail->Subject = "Verify your new ".SITE_NAME." account";
				$mail->Body = "<html><body><div><p>Hello ".Session::get('uname').", to verify your new ".SITE_NAME." account, enter this code: <strong style='color:#00a1ff;'>".$gen_random_int."</strong></p>
				<div style='margin-top:15px;'>Thanks,</div>
				<div>The ".SITE_NAME." Team</div></div></body></html>";

				if($mail->send()){
					$_messages[] = ["success" => "An email containing your verification code has been sent to $email. You may have to wait for 5 mins to resend your verification code if you have not received it. Can't find email? - do check in your spam folder."];
					$expiry = 60*5;
					Cookie::set('_vexp', 'true', $expiry);
					// Disable the button initially
					$disable_btn = 'disabled="disabled"';
				}else{
					$_messages[] = ["warning" => "An email containing your verification code could not be sent to $email. Try using another email address."];
				}
			}else{
				$_messages[] = ["danger" => $insertQuery->error_info()[2].": Unable to resend verification code."];
			}
		}
	}else{
		$_messages[] = ["warning" => "Invalid token"];
	}
}

if(Cookie::exists('_vexp')){
	$disable_btn = 'disabled="disabled"';
}

$post = post_values(['email' => $user_data['email']], 'resend');
?>

<!DOCTYPE html>
<html lang="en" dir="ltr" prefix="website: http://ogp.me/ns/website#">
	<head>
		<?php include_once __DIR__.'/includes/gtm.php'; ?>
		<title>Verify &#8208; <?=SITE_NAME?></title>
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
				<div class="col-12 col-sm-10 offset-sm-1">

					<?php if(!isset($_GET['resend'])): ?>
					
						<h2 class="text-center s-blue">Verify your account</h2>
						<h3 class="text-center s-black">Step 2 of 3</h3>
						<form role="form" action="" method="post" enctype="multipart/form-data">
							<div class="form-row">
								<div class="form-group col-12">
									<label for="verification code">Enter your verification code</label>
									<input type="number" name="verification_code" required placeholder="Verification code" class="form-control"/>
								</div>
							</div>
							<input type="hidden" name="form_token" value="<?=Hash::hashUseHmac('sha256', get_page_name(), Session::get('_token'))?>"/>
							<div class="form-row">
								<div class="form-group col-6">
									<button type="submit" name="verify" class="btn btn-primary btn-block">Verify</button>
								</div>
								<div class="form-group col-6">
									<a href="<?=SROOT?>verify.php?resend=1" class="btn btn-default btn-block">Resend code</a>
								</div>
							</div>
						</form>

					<?php else: ?>

						<h2 class="text-center s-blue">Resend verification code</h2>
						<form role="form" action="" method="post" enctype="multipart/form-data">
							<div class="form-row">
								<div class="form-group col-12">
									<label for="email address">Email address</label>
									<input type="email" name="email" value="<?=$post['email']?>" required placeholder="someone@email.com" minlength="5" maxlength="100" class="form-control"/>
								</div>
							</div>
							<input type="hidden" name="form_token" value="<?=Hash::hashUseHmac('sha256', get_page_name(), Session::get('_token'))?>"/>
							<div class="form-row">
								<div class="form-group col-6">
									<input type="submit" name="resend" <?=$disable_btn?> class="btn btn-primary btn-block">Resend</button>
								</div>
								<div class="form-group col-6">
									<a href="<?=SROOT?>verify.php" class="btn btn-default btn-block">Go back to verify</a>
								</div>
							</div>
						</form>

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