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
// require_once __DIR__.'/vendor/autoload.php';

use Classes\{Cookie, Database, Datetime, Router, Session, Str, Validate};
// use PHPMailer\PHPMailer\PHPMailer;

$db = Database::getInstance();
$session = Session::startSession();
// $mail = new PHPMailer();

// Init variables
$_messages = [];
$_err = 0;
$enable_access_level = false;
$rank = 'editor';

// If user session still exists, then redirect to dashboard.
if(Session::exists('uid')) Router::redirect('dashboard.php');

$referrer = (Cookie::exists('_rc') && empty($_GET['token']) && empty($_GET['rank'])) ? Cookie::get('_rc') : '';

if(!empty($_GET['token']) && !empty($_GET['rank'])){
	$get_token = sanitize_input($_GET['token']);
	$get_rank = sanitize_input($_GET['rank']);
	if($get_token === ACCESS_LEVEL_TOKEN){
		if($get_rank === 'admin'){
			$enable_access_level = true;
			$rank = 'admin';
		}elseif($get_rank === 'moderator'){
			$enable_access_level = true;
			$rank = 'moderator';
		}
	}
}

// NOTICE!!! Remove this when email verification is enabled
$signup_step = ($rank !== 'editor') ? 4 : 3;

if(isset($_POST['signup'])){
	if(!Validate::comparePasswords($_POST['password'], $_POST['confirm_password'])){
		$_messages[] = ["warning" => "Passwords do not match."];
		$_err = 1;
	}
	if(Validate::checkDuplicates($_POST['uname'], '__users', 'uname') !== 0){
		$_messages[] = ["warning" => "The username you entered already exists."];
		$_err = 1;
	}
	if(Validate::checkDuplicates($_POST['email'], '__users', 'email') !== 0){
		$_messages[] = ["warning" => "The email you entered already exists."];
		$_err = 1;
	}
	if(Validate::checkDuplicates($_POST['phone'], '__users', 'phone') !== 0){
		$_messages[] = ["warning" => "The phone number you entered already exists."];
		$_err = 1;
	}
	if(!Validate::validateEmail($_POST['email'])){
		$_messages[] = ["warning" => "Invalid email address."];
		$_err = 1;
	}
	if(!isset($_POST['agreement_check'])){
		$_messages[] = ["warning" => "You must agree to the Terms of Service and consent to the use of cookies to continue."];
		$_err = 1;
	}

	$gen_random_str = Str::randomStr(10);
	while(Validate::checkDuplicates($gen_random_str, '__users', 'referral_code') > 0){
		$gen_random_str = Str::randomStr(10);
	}

	if(!$_err){
		$gen_random_int = Str::randomInt(5);

		$insertQuery = $db->insertQuery('__users', [
			'fname' => sanitize_input($_POST['fname']),
			'lname' => sanitize_input($_POST['lname']),
			'oname' => sanitize_input($_POST['oname']),
			'uname' => sanitize_input($_POST['uname']),
			'email' => sanitize_email($_POST['email']),
			'phone' => sanitize_input($_POST['phone']),
			'dob' => sanitize_input($_POST['dob']),
			'gender' => sanitize_input($_POST['gender']),
			'state' => sanitize_input($_POST['state']),
			'photo' => '',
			'bio' => sanitize_input($_POST['bio']),
			'rank' => $rank,
			'psd' => password_hash($_POST['password'], PASSWORD_DEFAULT),
			'reset_psd_expiry' => '',
			'auth_hash' => '',
			'date_added' => Datetime::timestamp(),
			'last_edited_on' => Datetime::timestamp(),
			'status' => 'active',
			'suspend_expiry' => '',
			'verification_code' => $gen_random_int,
			'referrer' => $referrer,
			'referral_code' => $gen_random_str,
			'referral_count' => 0,
			'total_balance' => 0,
			'account_number' => '',
			'bank' => '',
			'signup_step' => $signup_step
		]);
		if(!$insertQuery->error()){

			$_messages[] = ["success" => "Your account has been created successfully."];

			// $mail->isSMTP();
			// $mail->Host = MAIL_DEFAULT_HOST;
			// $mail->Port = MAIL_DEFAULT_PORT;
			// $mail->SMTPSecure = MAIL_DEFAULT_SMTPSECURE;
			// $mail->SMTPAuth = MAIL_DEFAULT_SMTPAUTH;
			// $mail->Username = MAIL_DEFAULT_USERNAME;
			// $mail->Password = MAIL_DEFAULT_PASSWORD;
			// $mail->CharSet = 'UTF-8';
			// $mail->setFrom(MAIL_DEFAULT_FROM, MAIL_DEFAULT_FROM_NAME);
			// $mail->addReplyTo(MAIL_DEFAULT_FROM, MAIL_DEFAULT_FROM_NAME);
			// $mail->addAddress($_POST['email']);
			// $mail->isHTML(true);
			// $mail->Subject = "Verify your new ".SITE_NAME." account";
			// $mail->Body = "<html><body><div><p>Hello and welcome to ".SITE_NAME.", to verify your new ".SITE_NAME." account, enter this code: <strong style='color:#00a1ff;'>".$gen_random_int."</strong></p>
			// <div style='margin-top:15px;'>Thanks,</div>
			// <div>The ".SITE_NAME." Team</div></div></body></html>";
			// if($mail->send()){
			// 	$_messages[] = ["success" => "An email containing your verification code has been sent to $_POST[email]. Can't find email? - do check in your spam folder."];
			// }else{
			// 	$_messages[] = ["warning" => "An email containing your verification code could not be sent to $_POST[email]. Sign in and try resending email."];
			// }

			$_messages[] = ["success" => "You are almost there! <a href='".SROOT."signin.php' class='alert-link'>Sign in</a> to complete your registration process."];
		}else{
			$_messages[] = ["danger" => $insertQuery->error_info()[2].": Unable to create your account."];
		}
	}
}

$post = post_values(['fname' => '', 'lname' => '', 'oname' => '', 'uname' => '', 'email' => '', 'phone' => '', 'dob' => '', 'gender' => '', 'state' => '', 'bio' => '', 'password' => '', 'confirm_password' => ''], 'signup');
?>

<!DOCTYPE html>
<html lang="en" dir="ltr" prefix="website: http://ogp.me/ns/website#">
	<head>
		<?php include_once __DIR__.'/includes/gtm.php'; ?>
		<title>Sign up &#8208; <?=SITE_NAME?></title>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="theme-color" content="#000000">
		<link rel="icon" href="<?=SROOT?>favicon.png" sizes="19x19" type="image/png">
		<link rel="apple-touch-icon" href="<?=SROOT?>assets/icons/icon.png" type="image/png">
		<link rel="stylesheet" type="text/css" href="<?=SROOT?>assets/css/style.css?v=20200407">
		<meta name="robots" content="index">
		<meta name="description" content="Create a <?=SITE_NAME?> account to get started">
		<link rel="canonical" href="<?=SITE_URL.SROOT.get_page_name().'.php'?>">
		<meta property="og:title" content="Sign up &#8208; <?=SITE_NAME?>">
		<meta property="og:type" content="website">
		<meta property="og:image" content="<?=SITE_URL.SROOT?>assets/icons/icon.png">
		<meta property="og:image:type" content="image/png">
		<meta property="og:image:width" content="146">
		<meta property="og:image:height" content="146">
		<meta property="og:url" content="<?=SITE_URL.SROOT.get_page_name().'.php'?>">
		<meta property="og:description" content="Create a <?=SITE_NAME?> account to get started">
		<meta property="og:locale" content="en_US">
		<meta property="og:site_name" content="<?=SITE_NAME?>">
		<meta name="twitter:card" content="summary">
		<meta name="twitter:site" content="">
		<meta name="twitter:title" content="Sign up &#8208; <?=SITE_NAME?>">
		<meta name="twitter:description" content="Create a <?=SITE_NAME?> account to get started">
		<meta name="twitter:image" content="<?=SITE_URL.SROOT?>assets/icons/icon.png">
	</head>
	<body>
		<?php include_once __DIR__.'/includes/gtm-ns.php'; ?>
		<div class="min-vh-100">
			<?php include_once __DIR__.'/includes/nav.php';
				alert_messages($_messages);
			?>
	
			<div class="container mt-5">
				<div class="col-12 col-sm-10 offset-sm-1">
					<?php if($enable_access_level): ?>
						<h6 class="text-center">Access level: <?=$rank?></h6>
					<?php endif; ?>
					<h2 class="text-center s-blue">Create your account</h2>
					<h3 class="text-center s-black">Step 1 of 2</h3>
					<p class="text-muted text-center">All fields marked <span class="text-danger">*</span> are required</p>
					<form role="form" action="" method="post" enctype="multipart/form-data">
						<div class="form-row">
							<div class="form-group col-6">
								<label for="first name">First name <span class="text-danger">*</span></label>
								<input type="text" name="fname" value="<?=$post['fname']?>" required placeholder="John" minlength="2" maxlength="50" pattern="[A-Za-z\- ]{2,50}" title="Must contain letters, dashes or spaces only" class="form-control"/>
							</div>
							<div class="form-group col-6">
								<label for="last name">Last name <span class="text-danger">*</span></label>
								<input type="text" name="lname" value="<?=$post['lname']?>" required placeholder="Wick" minlength="2" maxlength="50" pattern="[A-Za-z\- ]{2,50}" title="Must contain letters, dashes or spaces only" class="form-control"/>
							</div>
						</div>
						<div class="form-row">
							<div class="form-group col-6">
								<label for="other names">Other names</label>
								<input type="text" name="oname" value="<?=$post['oname']?>" placeholder="Genie" minlength="2" maxlength="50" pattern="[A-Za-z\- ]{2,50}" title="Must contain letters, dashes or spaces only" class="form-control"/>
							</div>
							<div class="form-group col-6">
								<label for="username">Username <span class="text-danger">*</span></label>
								<input type="text" name="uname" value="<?=$post['uname']?>" required placeholder="TheRealJohn" minlength="3" maxlength="50" pattern="[A-Za-z0-9._-]{3,50}" title="Must contain not less than 3 and not more than 50 letters, numbers or characters(._-) only" class="form-control"/>
								<small class="form-text text-muted">You won't be able to make changes to the <strong>username</strong> provided once it is saved. Ensure you check for errors before saving.</small>
							</div>
						</div>
						<div class="form-row">
							<div class="form-group col-6">
								<label for="email address">Email address <span class="text-danger">*</span></label>
								<input type="email" name="email" value="<?=$post['email']?>" required placeholder="someone@email.com" minlength="5" maxlength="100" class="form-control"/>
								<small class="form-text text-muted">The <strong>email</strong> provided must exist and can receive mails.</small>
							</div>
							<div class="form-group col-6">
								<label for="phone">Phone <span class="text-danger">*</span></label>
								<input type="tel" name="phone" value="<?=$post['phone']?>" required placeholder="08123456789" minlength="10" maxlength="15" pattern="\+?[0-9]{10,20}" title="Must contain numbers with or without a + sign at the beginning" class="form-control"/>
							</div>
						</div>
						<div class="form-row">
							<div class="form-group col-6">
								<label for="date of birth">Date of birth <span class="text-danger">*</span></label>
								<input type="date" name="dob" value="<?=$post['dob']?>" required class="form-control">
							</div>
							<div class="form-group col-6">
								<label for="gender">Gender <span class="text-danger">*</span></label>
								<select name="gender" required class="form-control" id="selectGender">
									<option value="" disabled="disabled">Select an option</option>
									<option value="male">Male</option>
									<option value="female">Female</option>
									<option value="other">Rather not say</option>
								</select>
							</div>
						</div>
						<div class="form-row">
							<div class="form-group col-12">
								<label for="state">State <span class="text-danger">*</span></label>
								<select name="state" required class="form-control" id="selectState">
									<option value="" disabled="disabled">Select an option</option>
									<option value="abia">Abia</option>
									<option value="adamawa">Adamawa</option>
									<option value="akwa ibom">Akwa Ibom</option>
									<option value="anambra">Anambra</option>
									<option value="bauchi">Bauchi</option>
									<option value="bayelsa">Bayelsa</option>
									<option value="benue">Benue</option>
									<option value="borno">Borno</option>
									<option value="cross river">Cross River</option>
									<option value="delta">Delta</option>
									<option value="ebonyi">Ebonyi</option>
									<option value="edo">Edo</option>
									<option value="ekiti">Ekiti</option>
									<option value="enugu">Enugu</option>
									<option value="gombe">Gombe</option>
									<option value="imo">Imo</option>
									<option value="jigawa">Jigawa</option>
									<option value="kaduna">Kaduna</option>
									<option value="kano">Kano</option>
									<option value="kastina">Kastina</option>
									<option value="kebbi">Kebbi</option>
									<option value="kogi">Kogi</option>
									<option value="kwara">Kwara</option>
									<option value="lagos">Lagos</option>
									<option value="nasarawa">Nasarawa</option>
									<option value="niger">Niger</option>
									<option value="ogun">Ogun</option>
									<option value="ondo">Ondo</option>
									<option value="osun">Osun</option>
									<option value="oyo">Oyo</option>
									<option value="plateau">Plateau</option>
									<option value="rivers">Rivers</option>
									<option value="sokoto">Sokoto</option>
									<option value="taraba">Taraba</option>
									<option value="yobe">Yobe</option>
									<option value="zamfara">Zamfara</option>
								</select>
							</div>
						</div>
						<div class="form-row">
							<div class="form-group col-12">
								<label for="bio">Bio <span class="text-danger">*</span></label>
								<textarea name="bio" required placeholder="Information about yourself..." maxlength="1000" class="form-control" style="resize:vertical;"><?=$post['bio']?></textarea>
								<small class="form-text text-muted">Not more than 1000 characters</small>
							</div>
						</div>
						<div class="form-row">
							<div class="form-group col-6">
								<label for="password">Password <span class="text-danger">*</span></label>
								<input type="password" name="password" value="<?=$post['password']?>" required placeholder="Password" minlength="6" class="form-control"/>
								<small class="form-text text-muted">Use 6 or more characters with a mix of letters, numbers & symbols</small>
							</div>
							<div class="form-group col-6">
								<label for="confirm password">Confirm password <span class="text-danger">*</span></label>
								<input type="password" name="confirm_password" value="<?=$post['confirm_password']?>" required placeholder="Confirm password" minlength="6" class="form-control"/>
							</div>
						</div>
						<div class="form-row">
							<div class="form-group col-12">
								<div class="form-check">
									<input type="checkbox" name="agreement_check" class="form-check-input"/>
									<label class="form-check-label small" for="keep me signed in on this device">By checking this, you confirm that you have read and agreed to the <a href="<?=SROOT?>terms.php">Terms</a> of Service and Privacy Policies provided and consent to the use of cookies.</label>
								</div>
							</div>
						</div>
						<div class="form-row">
							<div class="form-group col-12">
								<button type="submit" name="signup" class="btn btn-primary btn-block">I'm in!</button>
							</div>
						</div>
					</form>
					<div class="form-group text-center">
						<a href="<?=SROOT?>signin.php" class="text-primary">Already have an account? Sign in</a>
					</div>
				</div>
			</div>
		</div>

		<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
		<script src="https://unpkg.com/popper.js@1.12.6/dist/umd/popper.js" integrity="sha384-fA23ZRQ3G/J53mElWqVJEGJzU0sTs+SvzG8fXVWP+kJQ1lwFAOkcUOysnlKJC33U" crossorigin="anonymous"></script>
		<script type="text/javascript" src="<?=SROOT?>assets/bootstrap-material-design-4.1.1-dist/js/bootstrap-material-design.min.js"></script>
		<script type="text/javascript" src="<?=SROOT?>assets/js/script.js?v=20200414"></script>
		<script type="text/javascript">
			optionSelected('selectGender', '<?=$post['gender']?>');
			optionSelected('selectState', '<?=$post['state']?>');
		</script>
	</body>
</html>