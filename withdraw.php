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

// Init variables
$_messages = [];
$_allowed = 1;

// If user session doesn't exist, then redirect to sign in.
if(!Session::exists('uid')) Router::redirect('signin.php?next='.urlencode(SROOT.get_page_name().'.php'));

// Require logic.
require_once __DIR__.'/includes/logic.php';

// Check if subscription is still active
if(!checkSubscription(Session::get('uid'))){
	$_messages[] = ["warning" => "Unable to request for withdrawal - No active subscription found."];
	$_allowed = 0;
}
// Check if there are pending withdrawals
$account_data_count = $db->selectQuery('__accounts', ['id'], ['WHERE' => ['user_id' => Session::get('uid'), 'status' => 'pending']])->row_count();
if($account_data_count > 0){
	$_messages[] = ["warning" => "Unable to request for withdrawal - Pending withdrawals found."];
	$_allowed = 0;
}
// Check if bank details are filled
$user_data = $db->selectQuery('__users', ['account_number', 'bank'], ['WHERE' => ['id' => Session::get('uid')]])->results();
if(empty($user_data['account_number']) || empty($user_data['bank'])){
	$_messages[] = ["warning" => "Unable to request for withdrawal - Bank details have not been filled. Fill in your bank details <a href='".SROOT."settings.php' class='alert-link'>here</a>."];
	$_allowed = 0;
}


if(isset($_POST['confirm']) && Validate::validateInt($_POST['amount'])){
	if(Hash::compareHashes(Hash::hashUseHmac('sha256', get_page_name(), Session::get('_token')), $_POST['form_token'])){
		// Check threshold
		if($_POST['amount'] < 3000){
			$_messages[] = ["warning" => "Unable to request for withdrawal - <span aria-hidden='true'>&#8358;</span>$_POST[amount] is less than threshold amount of <span aria-hidden='true'>&#8358;</span>3000."];
			$_allowed = 0;
		}
		// Check if withdrawed amount is > total balance
		$user_data = $db->selectQuery('__users', ['total_balance'], ['WHERE' => ['id' => Session::get('uid')]])->results();
		if($_POST['amount'] > $user_data['total_balance']){
			$_messages[] = ["warning" => "Unable to request for withdrawal - <span aria-hidden='true'>&#8358;</span>$_POST[amount] is greater than current total balance of <span aria-hidden='true'>&#8358;</span>$user_data[total_balance]."];
			$_allowed = 0;
		}

		if($_allowed){
			if(addToAccount(Session::get('uid'), 'debit', '', sanitize_int($_POST['amount']), 'pending')){
				$_messages[] = ["success" => "Withdrawal request of <strong><span aria-hidden='true'>&#8358;</span>$_POST[amount]</strong> has been sent, you'll have to wait for up to 48hrs for an admin to handle the request and credit your bank account. Please note, you won't be able to request for another withdrawal until all pending withdrawal request are handled."];
			}else{
				$_messages[] = ["danger" => $insertQuery->error_info()[2].": Unable to request withdrawal."];
			}
		}
	}else{
		$_messages[] = ["warning" => "Invalid token"];
	}
}

$post = post_values(['amount' => ''], 'confirm');
?>

<!DOCTYPE html>
<html lang="en" dir="ltr" prefix="website: http://ogp.me/ns/website#">
	<head>
		<?php include_once __DIR__.'/includes/gtm.php'; ?>
		<title>Withdraw &#8208; <?=SITE_NAME?></title>
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
					<h2 class="text-center s-blue">Withdraw</h2>
					<form role="form" action="" method="post" enctype="multipart/form-data">
						<div class="form-group">
							<p class="form-text text-muted">Enter the amount you would like to withdraw and click the <strong>request</strong> button:</p>
							<div class="input-group">
								<span class="input-group-addon"><span aria-hidden="true">&#8358;</span></span>
								<input type="number" name="amount" value="<?=$post['amount']?>" required placeholder="3000" class="form-control" aria-label="Amount">
								<span class="input-group-addon">.00</span>
							</div>
							<small class="form-text text-muted">Withdraw threshold: <span aria-hidden="true">&#8358;</span>3000</small>
						</div>
						<input type="hidden" name="form_token" value="<?=Hash::hashUseHmac('sha256', get_page_name(), Session::get('_token'))?>"/>
						<?php if($_allowed): ?>
							<div class="form-group">
								<button type="submit" name="confirm" class="btn btn-success btn-lg">Request</button>
							</div>
						<?php endif; ?>
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