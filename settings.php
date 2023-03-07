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

use Classes\{Database, Datetime, Hash, Router, Session, Validate};

$db = Database::getInstance();
$session = Session::startSession();

// Init variable
$_messages = [];
$_err = 0;
$readonly_input = $disable_btn = '';

// If user session doesn't exist, then redirect to sign in.
if(!Session::exists('uid')) Router::redirect('signin.php?next='.urlencode(SROOT.get_page_name().'.php'));

// Require logic.
require_once __DIR__.'/includes/logic.php';

$selectQueryUser = $db->selectQuery('__users', ['fname', 'lname', 'oname', 'email', 'phone', 'bio', 'account_number', 'bank'], ['WHERE' => ['id' => Session::get('uid')]]);
$data = $selectQueryUser->results();

if(!empty($data['account_number'])){
	$readonly_input = 'readonly';
	$disable_btn = 'disabled="disabled"';
}

// Update bank details
if(isset($_POST['save_bank'])){
	if(Hash::compareHashes(Hash::hashUseHmac('sha256', get_page_name(), Session::get('_token')), $_POST['form_token'])){
		if(Validate::checkDuplicates($_POST['account_number'], '__users', 'account_number') > 0){
			$_messages[] = ["warning" => "The account number you entered already exists."];
			$_err = 1;
		}
		if(!$_err){
			$updateQueryBank = $db->updateQuery('__users', [
				'account_number' => sanitize_int($_POST['account_number']),
				'bank' => sanitize_input($_POST['bank']),
				'last_edited_on' => Datetime::timestamp()], ['id' => Session::get('uid')]);
			if(!$updateQueryBank->error()){
				$_messages[] = ["success" => "Bank details successfully saved"];
				$readonly_input = 'readonly';
				$disable_btn = 'disabled="disabled"';
			}else{
				$_messages[] = ["danger" => $updateQueryPhoto->error_info()[2].": Unable to save bank details."];
			}
		}
	}else{
		$_messages[] = ["warning" => "Invalid token"];
	}
}

// Update bio
if(isset($_POST['save_bio'])){
	if(Hash::compareHashes(Hash::hashUseHmac('sha256', get_page_name(), Session::get('_token')), $_POST['form_token'])){
		$updateQueryBio = $db->updateQuery('__users', [
			'bio' => sanitize_input($_POST['bio']),
			'last_edited_on' => Datetime::timestamp()], ['id' => Session::get('uid')]);
		if(!$updateQueryBio->error()){
			$_messages[] = ["success" => "Your bio was successfully updated"];
		}else{
			$_messages[] = ["danger" => $updateQueryPhoto->error_info()[2].": Unable to update bio."];
		}
	}else{
		$_messages[] = ["warning" => "Invalid token"];
	}
}

// Update names
if(isset($_POST['save_name'])){
	if(Hash::compareHashes(Hash::hashUseHmac('sha256', get_page_name(), Session::get('_token')), $_POST['form_token'])){
		$updateQueryPhoto = $db->updateQuery('__users', [
			'fname' => sanitize_input($_POST['fname']),
			'lname' => sanitize_input($_POST['lname']),
			'oname' => sanitize_input($_POST['oname']),
			'last_edited_on' => Datetime::timestamp()], ['id' => Session::get('uid')]);
		if(!$updateQueryPhoto->error()){
			$_messages[] = ["success" => "Your name was successfully updated"];
		}else{
			$_messages[] = ["danger" => $updateQueryPhoto->error_info()[2].": Unable to update your name."];
		}
	}else{
		$_messages[] = ["warning" => "Invalid token"];
	}
}

// Update email and phone
if(isset($_POST['save_email_phone'])){
	if(Hash::compareHashes(Hash::hashUseHmac('sha256', get_page_name(), Session::get('_token')), $_POST['form_token'])){
		if(Validate::checkDuplicates($_POST['email'], '__users', 'email') > 0 && $_POST['email'] !== $data['email']){
			$_messages[] = ["warning" => "The email you entered already exists"];
			$_err = 1;
		}
		if(Validate::checkDuplicates($_POST['phone'], '__users', 'phone') > 0 && $_POST['phone'] !== $data['phone']){
			$_messages[] = ["warning" => "The mobile number you entered already exists"];
			$_err = 1;
		}
		if(!$_err){
			$updateQueryEmailPhone = $db->updateQuery('__users', [
				'email' => sanitize_email($_POST['email']),
				'phone' => sanitize_input($_POST['phone']),
				'last_edited_on' => Datetime::timestamp()], ['id' => Session::get('uid')]);
			if(!$updateQueryEmailPhone->error()){
				$_messages[] = ["success" => "Your email and phone was successfully updated"];
			}else{
				$_messages[] = ["danger" => $updateQueryEmailPhone->error_info()[2].": Unable to update email and phone."];
			}
		}
	}else{
		$_messages[] = ["warning" => "Invalid token"];
	}
}

$post1 = post_values(['account_number' => $data['account_number'], 'bank' => $data['bank']], 'save_bank');
$post2 = post_values(['bio' => $data['bio']], 'save_bio');
$post3 = post_values(['fname' => $data['fname'], 'lname' => $data['lname'], 'oname' => $data['oname']], 'save_name');
$post4 = post_values(['email' => $data['email'], 'phone' => $data['phone']], 'save_email_phone');
?>

<!DOCTYPE html>
<html lang="en" dir="ltr" prefix="website: http://ogp.me/ns/website#">
	<head>
		<?php include_once __DIR__.'/includes/gtm.php'; ?>
		<title>Settings &#8208; <?=SITE_NAME?></title>
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
				<h2 class="text-center s-blue">Settings</h2>
				<!-- Bank details -->
				<div class="col-12 col-sm-10 offset-sm-1">
					<h3>Bank details</h3>
					<p class="text-muted">You won't be able to make changes to the <strong>account number & bank</strong> provided once it is saved. Ensure you check for errors before saving. <a href="<?=SROOT?>learn-more.php?q=bank-details" target="_blank">Learn more</a></p>
					<form role="form" action="" method="post" enctype="multipart/form-data">
						<div class="form-row">
							<div class="form-group col-6">
								<label for="account number">Account number</label>
								<input type="number" name="account_number" value="<?=$post1['account_number']?>" required placeholder="0123456789" maxlength="10" <?=$readonly_input?> class="form-control"/>
							</div>
							<div class="form-group col-6">
								<label for="bank">Bank</label>
								<select name="bank" required <?=$readonly_input?> class="form-control" id="selectBank">
									<option value="" disabled="disabled">Select an option</option>
									<option value="access-bank">Access Bank</option>
									<option value="access-bank-diamond">Access Bank (Diamond)</option>
									<option value="citibank-nigeria">Citibank</option>
									<option value="ecobank-nigeria">Ecobank</option>
									<option value="fidelity-bank">Fidelity Bank</option>
									<option value="first-city-monument-bank">First City Monument Bank</option>
									<option value="first-bank-of-nigeria">First Bank</option>
									<option value="globus-bank">Globus Bank</option>
									<option value="guaranty-trust-bank">Guaranty Trust Bank</option>
									<option value="heritage-bank">Heritage Bank</option>
									<option value="keystone-bank">Keystone Bank</option>
									<option value="polaris-bank">Polaris Bank</option>
									<option value="providus-bank">Providus Bank</option>
									<option value="stanbic-ibtc-bank">Stanbic IBTC Bank</option>
									<option value="standard-chartered-bank">Standard Chartered Bank</option>
									<option value="sterling-bank">Sterling Bank</option>
									<option value="suntrust-bank">SunTrust Bank</option>
									<option value="titan-trust bank">Titan Trust Bank</option>
									<option value="united-bank-for-africa">United Bank For Africa (UBA)</option>
									<option value="union-bank-of-nigeria">Union Bank</option>
									<option value="unity-bank">Unity Bank</option>
									<option value="wema-bank">Wema Bank</option>
									<option value="zenith-bank">Zenith Bank</option>
								</select>
							</div>
						</div>
						<input type="hidden" name="form_token" value="<?=Hash::hashUseHmac('sha256', get_page_name(), Session::get('_token'))?>"/>
						<div class="form-row">
							<div class="form-group col-12">
								<button type="submit" name="save_bank" <?=$disable_btn?> class="btn btn-success">Save changes</button>
							</div>
						</div>
					</form>
				</div>

				<!-- Profile details -->
				<div class="col-12 col-sm-10 offset-sm-1">
					<h3>Profile details</h3>
					<form role="form" action="" method="post" enctype="multipart/form-data">
						<div class="form-row">
							<div class="form-group col-12">
								<label for="Bio">Bio</label>
								<textarea name="bio" required placeholder="Information about yourself..." maxlength="1000" class="form-control" style="resize:vertical;"><?=$post2['bio']?></textarea>
								<small class="form-text text-muted">Not more than 1000 characters</small>
							</div>
						</div>
						<input type="hidden" name="form_token" value="<?=Hash::hashUseHmac('sha256', get_page_name(), Session::get('_token'))?>"/>
						<div class="form-row">
							<div class="form-group col-12">
								<button type="submit" name="save_bio" class="btn btn-success">Update</button>
							</div>
						</div>
					</form>

					<form role="form" action="" method="post" enctype="multipart/form-data">
						<div class="form-row">
							<div class="form-group col-6 col-sm-4">
								<label for="first name">First name</label>
								<input type="text" name="fname" value="<?=$post3['fname']?>" required placeholder="John" minlength="2" maxlength="50" pattern="[A-Za-z\- ]{2,50}" title="Must contain letters, dashes or spaces only" class="form-control"/>
							</div>
							<div class="form-group col-6 col-sm-4">
								<label for="last name">Last name</label>
								<input type="text" name="lname" value="<?=$post3['lname']?>" required placeholder="Wick" minlength="2" maxlength="50" pattern="[A-Za-z\- ]{2,50}" title="Must contain letters, dashes or spaces only" class="form-control"/>
							</div>
							<div class="form-group col-12 col-sm-4">
								<label for="other names">Other names</label>
								<input type="text" name="oname" value="<?=$post3['oname']?>" placeholder="Genie" minlength="2" maxlength="50" pattern="[A-Za-z\- ]{2,50}" title="Must contain letters, dashes or spaces only" class="form-control"/>
							</div>
						</div>
						<input type="hidden" name="form_token" value="<?=Hash::hashUseHmac('sha256', get_page_name(), Session::get('_token'))?>"/>
						<div class="form-row">
							<div class="form-group col-12">
								<button type="submit" name="save_name" class="btn btn-success">Save changes</button>
							</div>
						</div>
					</form>

					<form role="form" action="" method="post" enctype="multipart/form-data">
						<div class="form-row">
							<div class="form-group col-6">
								<label for="email address">Email address</label>
								<input type="email" name="email" value="<?=$post4['email']?>" required placeholder="someone@email.com" minlength="5" maxlength="100" class="form-control"/>
							</div>
							<div class="form-group col-6">
								<label for="phone">Phone</label>
								<input type="tel" name="phone" value="<?=$post4['phone']?>" required placeholder="08123456789" minlength="10" maxlength="20" pattern="\+?[0-9]{10,20}" title="Must contain numbers with or without a + sign at the beginning" class="form-control"/>
							</div>
						</div>
						<input type="hidden" name="form_token" value="<?=Hash::hashUseHmac('sha256', get_page_name(), Session::get('_token'))?>"/>
						<div class="form-row">
							<div class="form-group col-12">
								<button type="submit" name="save_email_phone" class="btn btn-success">Save changes</button>
							</div>
						</div>
						<div class="form-row">
							<div class="form-group col-12">
								<a href="<?=SROOT?>signout.php?next=<?=urlencode(SROOT."forgot-password.php?from=settings")?>" class="btn btn-default">Change password</a>
								<small class="text-muted">You will be signed out</small>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>

		<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
		<script src="https://unpkg.com/popper.js@1.12.6/dist/umd/popper.js" integrity="sha384-fA23ZRQ3G/J53mElWqVJEGJzU0sTs+SvzG8fXVWP+kJQ1lwFAOkcUOysnlKJC33U" crossorigin="anonymous"></script>
		<script type="text/javascript" src="<?=SROOT?>assets/bootstrap-material-design-4.1.1-dist/js/bootstrap-material-design.min.js"></script>
		<script type="text/javascript" src="<?=SROOT?>assets/js/script.js?v=20200414"></script>
		<script type="text/javascript">
			optionSelected('selectBank', '<?=$post1['bank']?>');
		</script>
	</body>
</html>