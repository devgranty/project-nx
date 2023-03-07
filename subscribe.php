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

use Classes\{Database, Datetime, Hash, Router, Session};

$db = Database::getInstance();
$session = Session::startSession();

// Init variables
$_messages = [];

// If user session doesn't exist, then redirect to sign in.
if(!Session::exists('uid')) Router::redirect('signin.php?next='.urlencode(SROOT.get_page_name().'.php'));

// Require logic.
require_once __DIR__.'/includes/logic.php';

// Redirect user if account is activated, disallow user till account expires
if(checkSubscription(Session::get('uid'))) Router::redirect('dashboard.php?rdr=1&msg='.get_page_name());

if(isset($_POST['verify_sub_key'])){
	if(Hash::compareHashes(Hash::hashUseHmac('sha256', get_page_name(), Session::get('_token')), $_POST['form_token'])){
		$sub_key_data = $db->selectQuery('__sub_keys', ['id', 'plan'], ['WHERE' => ['sub_key' => sanitize_input($_POST['sub_key'])]]);
		$data = $sub_key_data->results();

		if($sub_key_data->row_count() > 0){
			$user_signup_step = $db->selectQuery('__users', ['signup_step'], ['WHERE' => ['id' => Session::get('uid')]])->results()['signup_step'];
			$first_subscription = ($user_signup_step == 3) ? 1 : 0;

			$insertQuery = $db->insertQuery('__subscriptions', [
				'user_id' => Session::get('uid'),
				'reference' => sanitize_input('M-'.$_POST['sub_key']),
				'plan' => $data['plan'],
				'subscription_date' => Datetime::timestamp(),
				'subscription_end' => Datetime::stringToTimestamp('+ 30 days'),
				'first_subscription' => $first_subscription
			]);

			if(!$insertQuery->error()){
				if($user_signup_step == 3){
					$db->updateQuery('__users', [
						'signup_step' => 4], ['id' => Session::get('uid')]);
				}
				// Delete sub key
				$db->deleteQuery('__sub_keys', ['id' => $data['id']]);
			}
			Router::redirect('dashboard.php?msg=activated');
		}else{
			$_messages[] = ["warning" => "Invalid subscription key."];
		}
	}else{
		$_messages[] = ["warning" => "Invalid token"];
	}
}

if(!empty($_GET['msg'])){
	switch($_GET['msg']){
		case 'verified':
			$_messages[] = ["success" => "Nicely done! Your ".SITE_NAME." account has been verified successfully, one more step and you are all set up!"];
		break;
	}
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr" prefix="website: http://ogp.me/ns/website#">
	<head>
		<?php include_once __DIR__.'/includes/gtm.php'; ?>
		<title>Subscribe &#8208; <?=SITE_NAME?></title>
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
					<h2 class="text-center s-blue">Activate your account</h2>
					<h3 class="text-center s-black">Step 2 of 2</h3>
					<h5 class="text-primary text-center">Our subscription plans</h5>
					<div class="row">
						<div class="col-12 col-md-6 mb-2">
							<div class="card text-center">
								<div class="card-body">
									<h5 class="card-title">TESTER</h5>
									<p class="card-text">Amount: <span aria-hidden="true">&#8358;</span><?=planAmount('tester')/100?> <span class="text-dark" aria-hidden="true">*</span> <span class="text-success" aria-hidden="true">*</span></p>
									<p class="card-text">Earn: <span aria-hidden="true">&#8358;</span><?=fetchSettings('tester_earn')?>/day <span class="text-success" aria-hidden="true">*</span></p>
									<p class="card-text"><?=fetchSettings('referral_percentage')?>% referral bonus <span class="text-success" aria-hidden="true">*</span></p>
									<a href="https://wa.me/2349025023170?text=<?=urlencode('Hello, I would like to activate my '.SITE_NAME.' account. Plan of choice is TESTER, My name is ___')?>" rel="external" target="_blank" class="btn btn-success btn-block"><i class="fab fa-whatsapp" aria-hidden="true"></i> Send to +2349025023170</a>
									<a href="https://wa.me/2348130289754?text=<?=urlencode('Hello, I would like to activate my '.SITE_NAME.' account. Plan of choice is GOLD, My name is ___')?>" rel="external" target="_blank" class="btn btn-success btn-block"><i class="fab fa-whatsapp" aria-hidden="true"></i> Send to +2348130289754</a>
								</div>
							</div>
						</div>
						<div class="col-12 col-md-6 mb-2">
							<div class="card text-center">
								<div class="card-body">
									<h5 class="card-title">BASIC</h5>
									<p class="card-text">Amount: <span aria-hidden="true">&#8358;</span><?=planAmount('basic')/100?> <span class="text-dark" aria-hidden="true">*</span> <span class="text-success" aria-hidden="true">*</span></p>
									<p class="card-text">Earn: <span aria-hidden="true">&#8358;</span><?=fetchSettings('basic_earn')?>/day <span class="text-success" aria-hidden="true">*</span></p>
									<p class="card-text"><?=fetchSettings('referral_percentage')?>% referral bonus <span class="text-success" aria-hidden="true">*</span></p>
									<a href="https://wa.me/2349025023170?text=<?=urlencode('Hello, I would like to activate my '.SITE_NAME.' account. Plan of choice is BASIC, My name is ___')?>" rel="external" target="_blank" class="btn btn-success btn-block"><i class="fab fa-whatsapp" aria-hidden="true"></i> Send to +2349025023170</a>
									<a href="https://wa.me/2348130289754?text=<?=urlencode('Hello, I would like to activate my '.SITE_NAME.' account. Plan of choice is GOLD, My name is ___')?>" rel="external" target="_blank" class="btn btn-success btn-block"><i class="fab fa-whatsapp" aria-hidden="true"></i> Send to +2348130289754</a>
								</div>
							</div>
						</div>
						<div class="col-12 col-md-6 mb-2">
							<div class="card text-center">
								<div class="card-body">
									<h5 class="card-title">BRONZE</h5>
									<p class="card-text">Amount: <span aria-hidden="true">&#8358;</span><?=planAmount('bronze')/100?> <span class="text-dark" aria-hidden="true">*</span> <span class="text-success" aria-hidden="true">*</span></p>
									<p class="card-text">Earn: <span aria-hidden="true">&#8358;</span><?=fetchSettings('bronze_earn')?>/day <span class="text-success" aria-hidden="true">*</span></p>
									<p class="card-text"><?=fetchSettings('referral_percentage')?>% referral bonus <span class="text-success" aria-hidden="true">*</span></p>
									<a href="https://wa.me/2349025023170?text=<?=urlencode('Hello, I would like to activate my '.SITE_NAME.' account. Plan of choice is BRONZE, My name is ___')?>" rel="external" target="_blank" class="btn btn-success btn-block"><i class="fab fa-whatsapp" aria-hidden="true"></i> Send to +2349025023170</a>
									<a href="https://wa.me/2348130289754?text=<?=urlencode('Hello, I would like to activate my '.SITE_NAME.' account. Plan of choice is GOLD, My name is ___')?>" rel="external" target="_blank" class="btn btn-success btn-block"><i class="fab fa-whatsapp" aria-hidden="true"></i> Send to +2348130289754</a>
								</div>
							</div>
						</div>
						<div class="col-12 col-md-6 mb-2">
							<div class="card text-center">
								<div class="card-body">
									<h5 class="card-title">SILVER</h5>
									<p class="card-text">Amount: <span aria-hidden="true">&#8358;</span><?=planAmount('silver')/100?> <span class="text-dark" aria-hidden="true">*</span> <span class="text-success" aria-hidden="true">*</span></p>
									<p class="card-text">Earn: <span aria-hidden="true">&#8358;</span><?=fetchSettings('silver_earn')?>/day <span class="text-success" aria-hidden="true">*</span></p>
									<p class="card-text"><?=fetchSettings('referral_percentage')?>% referral bonus <span class="text-success" aria-hidden="true">*</span></p>
									<a href="https://wa.me/2349025023170?text=<?=urlencode('Hello, I would like to activate my '.SITE_NAME.' account. Plan of choice is SILVER, My name is ___')?>" rel="external" target="_blank" class="btn btn-success btn-block"><i class="fab fa-whatsapp" aria-hidden="true"></i> Send to +2349025023170</a>
									<a href="https://wa.me/2348130289754?text=<?=urlencode('Hello, I would like to activate my '.SITE_NAME.' account. Plan of choice is GOLD, My name is ___')?>" rel="external" target="_blank" class="btn btn-success btn-block"><i class="fab fa-whatsapp" aria-hidden="true"></i> Send to +2348130289754</a>
								</div>
							</div>
						</div>
						<div class="col-12 col-md-6 mb-2">
							<div class="card text-center">
								<div class="card-body">
									<h5 class="card-title">EMERALD</h5>
									<p class="card-text">Amount: <span aria-hidden="true">&#8358;</span><?=planAmount('emerald')/100?> <span class="text-dark" aria-hidden="true">*</span> <span class="text-success" aria-hidden="true">*</span></p>
									<p class="card-text">Earn: <span aria-hidden="true">&#8358;</span><?=fetchSettings('emerald_earn')?>/day <span class="text-success" aria-hidden="true">*</span></p>
									<p class="card-text"><?=fetchSettings('referral_percentage')?>% referral bonus <span class="text-success" aria-hidden="true">*</span></p>
									<a href="https://wa.me/2349025023170?text=<?=urlencode('Hello, I would like to activate my '.SITE_NAME.' account. Plan of choice is EMERALD, My name is ___')?>" rel="external" target="_blank" class="btn btn-success btn-block"><i class="fab fa-whatsapp" aria-hidden="true"></i> Send to +2349025023170</a>
									<a href="https://wa.me/2348130289754?text=<?=urlencode('Hello, I would like to activate my '.SITE_NAME.' account. Plan of choice is GOLD, My name is ___')?>" rel="external" target="_blank" class="btn btn-success btn-block"><i class="fab fa-whatsapp" aria-hidden="true"></i> Send to +2348130289754</a>
								</div>
							</div>
						</div>
						<div class="col-12 col-md-6 mb-2">
							<div class="card text-center">
								<div class="card-body">
									<h5 class="card-title">JASPER</h5>
									<p class="card-text">Amount: <span aria-hidden="true">&#8358;</span><?=planAmount('jasper')/100?> <span class="text-dark" aria-hidden="true">*</span> <span class="text-success" aria-hidden="true">*</span></p>
									<p class="card-text">Earn: <span aria-hidden="true">&#8358;</span><?=fetchSettings('jasper_earn')?>/day <span class="text-success" aria-hidden="true">*</span></p>
									<p class="card-text"><?=fetchSettings('referral_percentage')?>% referral bonus <span class="text-success" aria-hidden="true">*</span></p>
									<a href="https://wa.me/2349025023170?text=<?=urlencode('Hello, I would like to activate my '.SITE_NAME.' account. Plan of choice is JASPER, My name is ___')?>" rel="external" target="_blank" class="btn btn-success btn-block"><i class="fab fa-whatsapp" aria-hidden="true"></i> Send to +2349025023170</a>
									<a href="https://wa.me/2348130289754?text=<?=urlencode('Hello, I would like to activate my '.SITE_NAME.' account. Plan of choice is GOLD, My name is ___')?>" rel="external" target="_blank" class="btn btn-success btn-block"><i class="fab fa-whatsapp" aria-hidden="true"></i> Send to +2348130289754</a>
								</div>
							</div>
						</div>
						<div class="col-12 col-md-6 mb-2">
							<div class="card text-center">
								<div class="card-body">
									<h5 class="card-title">RUBY</h5>
									<p class="card-text">Amount: <span aria-hidden="true">&#8358;</span><?=planAmount('ruby')/100?> <span class="text-dark" aria-hidden="true">*</span> <span class="text-success" aria-hidden="true">*</span></p>
									<p class="card-text">Earn: <span aria-hidden="true">&#8358;</span><?=fetchSettings('ruby_earn')?>/day <span class="text-success" aria-hidden="true">*</span></p>
									<p class="card-text"><?=fetchSettings('referral_percentage')?>% referral bonus <span class="text-success" aria-hidden="true">*</span></p>
									<a href="https://wa.me/2349025023170?text=<?=urlencode('Hello, I would like to activate my '.SITE_NAME.' account. Plan of choice is RUBY, My name is ___')?>" rel="external" target="_blank" class="btn btn-success btn-block"><i class="fab fa-whatsapp" aria-hidden="true"></i> Send to +2349025023170</a>
									<a href="https://wa.me/2348130289754?text=<?=urlencode('Hello, I would like to activate my '.SITE_NAME.' account. Plan of choice is GOLD, My name is ___')?>" rel="external" target="_blank" class="btn btn-success btn-block"><i class="fab fa-whatsapp" aria-hidden="true"></i> Send to +2348130289754</a>
								</div>
							</div>
						</div>
						<div class="col-12 col-md-6 mb-2">
							<div class="card text-center">
								<div class="card-body">
									<h5 class="card-title">GOLD</h5>
									<p class="card-text">Amount: <span aria-hidden="true">&#8358;</span><?=planAmount('gold')/100?> <span class="text-dark" aria-hidden="true">*</span> <span class="text-success" aria-hidden="true">*</span></p>
									<p class="card-text">Earn: <span aria-hidden="true">&#8358;</span><?=fetchSettings('gold_earn')?>/day <span class="text-success" aria-hidden="true">*</span></p>
									<p class="card-text"><?=fetchSettings('referral_percentage')?>% referral bonus <span class="text-success" aria-hidden="true">*</span></p>
									<a href="https://wa.me/2349025023170?text=<?=urlencode('Hello, I would like to activate my '.SITE_NAME.' account. Plan of choice is GOLD, My name is ___')?>" rel="external" target="_blank" class="btn btn-success btn-block"><i class="fab fa-whatsapp" aria-hidden="true"></i> Send to +2349025023170</a>
									<a href="https://wa.me/2348130289754?text=<?=urlencode('Hello, I would like to activate my '.SITE_NAME.' account. Plan of choice is GOLD, My name is ___')?>" rel="external" target="_blank" class="btn btn-success btn-block"><i class="fab fa-whatsapp" aria-hidden="true"></i> Send to +2348130289754</a>
								</div>
							</div>
						</div>
					</div>

					<ul class="list-unstyled">
						<li class="text-muted mb-2">Step 1: Select a plan by clicking any of the WhatsApp numbers provided on plan, appending your name before sending the message on WhatsApp. An account number will be provided to you.</li>
						<li class="text-muted mb-2">Step 2: Once your transfer is verified, a 12-digit <em>SUBSCRIPTION KEY</em> will be sent to the <strong>Email address</strong> or <strong>WhatsApp number</strong> you provided.</li>
						<li class="text-muted mb-2">Step 3: Enter your subscription key.</li>
					<ul>

					<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#subKeyModal">Enter subscription key</button>

					<ul class="list-unstyled">
						<li class="small"><span class="text-dark" aria-hidden="true">*</span> <span class="font-italic">10% off for the first month</span></li>
						<li class="small"><span class="text-success" aria-hidden="true">*</span> <span class="font-italic">data is subject to change</span></li>
					</ul>
				</div>

				<div class="modal fade" id="subKeyModal" tabindex="-1" role="dialog" aria-labelledby="subKeyModalLabel" aria-hidden="true">
					<div class="modal-dialog" role="document">
						<div class="modal-content">
							<div class="modal-header">
								<h4 class="text-center">Enter your 12-digit subscription key</h4>
								<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria=hidden="true">&times;</span></button>
							</div>
							<div class="modal-body">
								<form role="form" action="" method="post" enctype="multipart/form-data">
									<div class="form-group">
										<div class="input-group">
											<div class="input-group-addon">M-</div>
											<input type="text" name="sub_key" required placeholder="XXXXXXXXXXXX" maxlength="12" class="form-control" onkeyup="this.value = this.value.toUpperCase();">
										</div>
									</div>
									<input type="hidden" name="form_token" value="<?=Hash::hashUseHmac('sha256', get_page_name(), Session::get('_token'))?>"/>
									<div class="form-group">
										<button type="submit" name="verify_sub_key" class="btn btn-success">Verify</button>
									</div>
								</form>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-secondary" data-dismiss="modal" aria-label="Close">Close</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
		<script src="https://unpkg.com/popper.js@1.12.6/dist/umd/popper.js" integrity="sha384-fA23ZRQ3G/J53mElWqVJEGJzU0sTs+SvzG8fXVWP+kJQ1lwFAOkcUOysnlKJC33U" crossorigin="anonymous"></script>
		<script type="text/javascript" src="<?=SROOT?>assets/bootstrap-material-design-4.1.1-dist/js/bootstrap-material-design.min.js"></script>
		<script type="text/javascript" src="<?=SROOT?>assets/js/script.js?v=20200414"></script>
	</body>
</html>