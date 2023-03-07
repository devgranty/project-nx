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
if(!Session::exists('uid')) Router::redirect('signin.php');
// Check if user is an editor
if(Session::get('rank') == 'editor') Router::redirect('403.php');

if(!empty($_GET['u'])){
	$user_id = sanitize_int($_GET['u']);
	
	$selectQueryUserComment = $db->selectQuery('__comments', ['id'], ['WHERE' => ['user_id' => $user_id, 'status' => 'approved']]);
	$user_comment_count = $selectQueryUserComment->row_count();

	$selectQueryUserPost = $db->selectQuery('__posts', ['id'], ['WHERE' => ['user_id' => $user_id, 'status' => 'approved']]);
	$user_post_count = $selectQueryUserPost->row_count();

    $selectQuery = $db->query('SELECT __users.id, __users.fname, __users.lname, __users.oname, __users.uname, __users.email, __users.phone, __users.dob, __users.gender, __users.state, __users.photo, __users.bio, __users.rank, __users.date_added, __users.last_edited_on, __users.status, __users.referral_count, __users.account_number, __users.bank, __users.signup_step FROM __users WHERE __users.id = ?', [$user_id]);
    if($selectQuery->row_count() <= 0){
        Router::redirect('404.php');
    }
    $data = $selectQuery->results();

    if(!empty($data['photo'])){
		$photo = IMG_CDN_URL.'photos/'.$data['photo'];
	}else{
		$photo = IMG_CDN_URL.'default/default-profile-picture-1280x1280.png';
	}

	if(isset($_POST['update_user_profile'])){
		if(Hash::compareHashes(Hash::hashUseHmac('sha256', get_page_name(), Session::get('_token')), $_POST['form_token'])){
			if($_POST['status'] == 'suspend 1 week'){
				$unix_time = Datetime::stringToTimestamp('+ 1 week');
			}elseif($_POST['status'] == 'suspend 1 month'){
				$unix_time = Datetime::stringToTimestamp('+ 1 month');
			}else{
				$unix_time = '';
			}
			$updateQuery = $db->updateQuery('__users', [
				'account_number' => sanitize_int($_POST['account_number']),
				'bank' => sanitize_input($_POST['bank']),
				'status' => sanitize_input($_POST['status']),
				'suspend_expiry' => $unix_time], ['id' => $user_id]);
			if(!$updateQuery->error()){
				$_messages[] = ["success" => "User's account with id:$user_id was updated successfully"];
			}else{
				$_messages[] = ["danger" => $updateQuery->error_info()[2].": Unable to update user account."];
			}
		}else{
			$_messages[] = ["warning" => "Invalid token"];
		}
	}

	if(isset($_POST['mark_all_reply_read'])){
		if(Hash::compareHashes(Hash::hashUseHmac('sha256', get_page_name(), Session::get('_token')), $_POST['form_token'])){
			$reply_update = $db->updateQuery('__replies', ['seen' => 1], ['user_id' => $user_id]);
			if(!$reply_update->error()){
				$_messages[] = ["success" => "Marked all replies from user with id:$user_id read."];
			}else{
				$_messages[] = ["danger" => $reply_update->error_info()[2].": Unable to mark replies as read."];
			}
		}else{
			$_messages[] = ["warning" => "Invalid token"];
		}
	}

	if(isset($_POST['credit_user'])){
		if(Hash::compareHashes(Hash::hashUseHmac('sha256', get_page_name(), Session::get('_token')), $_POST['form_token'])){
			if(addToAccount($user_id, 'credit', 'earned', ((commissionAmount(getPlan($user_id)))), 'paid')){
				$_messages[] = ["success" => "User with id:$user_id successfully credited."];
			}else{
				$_messages[] = ["warning" => "Unable to credit user with id:$user_id"];
			}
		}else{
			$_messages[] = ["warning" => "Invalid token"];
		}
	}
}else{
	Router::redirect('404.php');
}

$post = post_values(['fname' => $data['fname'], 'lname' => $data['lname'], 'oname' => $data['oname'], 'uname' => $data['uname'], 'email' => $data['email'], 'phone' => $data['phone'], 'dob' => $data['dob'], 'gender' => $data['gender'], 'state' => $data['state'], 'bio' => $data['bio'], 'rank' => $data['rank'], 'date_added' => Datetime::setDateTime($data['date_added']), 'last_edited_on' => Datetime::setDateTime($data['last_edited_on']), 'status' => $data['status'], 'referral_count' => $data['referral_count'], 'account_number' => $data['account_number'], 'bank' => $data['bank'], 'signup_step' => $data['signup_step']], 'update_user_profile');
?>

<!DOCTYPE html>
<html lang="en" dir="ltr" prefix="website: http://ogp.me/ns/website#">
	<head>
		<?php include_once __DIR__.'/includes/gtm.php'; ?>
		<title>User control &#8208; <?=SITE_NAME?></title>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="theme-color" content="#000000">
		<link rel="icon" href="<?=SROOT?>favicon.png" sizes="19x19" type="image/png">
		<link rel="apple-touch-icon" href="<?=SROOT?>assets/icons/icon.png" type="image/png">
		<link rel="stylesheet" type="text/css" href="<?=SROOT?>assets/css/style.css?v=20200407">
		<meta name="robots" content="noindex, nofollow">
		<style>
			.s-broadcast ul{list-style-position:inside;}
			.s-broadcast ol{list-style-position:inside;}
			.s-broadcast img{width:100%;}
		</style>
	</head>
	<body>
		<?php include_once __DIR__.'/includes/gtm-ns.php'; ?>
		<div class="min-vh-100">
			<?php include_once __DIR__.'/includes/nav.php';
				alert_messages($_messages);
			?>
			
			<div class="container mt-5">
				<div class="col-12 col-sm-10 offset-sm-1 mb-4">
					<h2 class="text-center s-blue">User ID:<?=$user_id?></h2>
					<div class="col-12 mb-4">
						<img src="<?=$photo?>" width="200" height="200" class="m-auto s-display-block rounded-circle s-pp-border-4x"/>
					</div>
					<form role="form" action="" method="post" enctype="multipart/form-data">
						<div class="form-row">
							<div class="form-group col-6">
								<label for="first name">First name</label>
								<input type="text" name="fname" value="<?=$post['fname']?>" readonly class="form-control"/>
							</div>
							<div class="form-group col-6">
								<label for="last name">Last name</label>
								<input type="text" name="lname" value="<?=$post['lname']?>" readonly class="form-control"/>
							</div>
						</div>
						<div class="form-row">
							<div class="form-group col-6">
								<label for="othername">Other names</label>
								<input type="text" name="oname" value="<?=$post['oname']?>" readonly class="form-control"/>
							</div>
							<div class="form-group col-6">
								<label for="username">Username</label>
								<input type="text" name="uname" value="<?=$post['uname']?>" readonly class="form-control"/>
							</div>
						</div>
						<div class="form-row">
							<div class="form-group col-6">
								<label for="email">Email address</label>
								<input type="text" name="email" value="<?=$post['email']?>" readonly class="form-control"/>
							</div>
							<div class="form-group col-6">
								<label for="phone">Phone</label>
								<input type="text" name="phone" value="<?=$post['phone']?>" readonly class="form-control"/>
							</div>
						</div>
						<div class="form-row">
							<div class="form-group col-6">
								<label for="dob">Date of birth</label>
								<input type="text" name="dob" value="<?=$post['dob']?>" readonly class="form-control"/>
							</div>
							<div class="form-group col-6">
								<label for="gender">Gender</label>
								<input type="text" name="gender" value="<?=$post['gender']?>" readonly class="form-control"/>
							</div>
						</div>
						<div class="form-row">
							<div class="form-group col-6">
								<label for="state">State</label>
								<input type="text" name="state" value="<?=$post['state']?>" readonly class="form-control"/>
							</div>
							<div class="form-group col-6">
								<label for="rank">Rank</label>
								<input type="text" name="rank" value="<?=$post['rank']?>" readonly class="form-control"/>
							</div>
						</div>
						<div class="form-row">
							<div class="form-group col-12">
								<label for="bio">Bio</label>
								<textarea name="bio" readonly class="form-control"><?=$post['bio']?></textarea>
							</div>
						</div>
						<div class="form-row">
							<div class="form-group col-6">
								<label for="date added">Date added</label>
								<input type="text" name="date_added" value="<?=$post['date_added']?>" readonly class="form-control"/>
							</div>
							<div class="form-group col-6">
								<label for="last edited on">Last edited on</label>
								<input type="text" name="last_edited_on" value="<?=$post['last_edited_on']?>" readonly class="form-control"/>
							</div>
						</div>
						<div class="form-row">
							<div class="form-group col-4">
								<label for="referral count">Referral count</label>
								<input type="text" name="referral_count" value="<?=$post['referral_count']?>" readonly class="form-control"/>
							</div>
							<div class="form-group col-4">
								<label for="comments">Comment count</label>
								<input type="text" name="" value="<?=$user_comment_count?>" readonly class="form-control"/>
							</div>
							<div class="form-group col-4">
								<label for="posts">Post count</label>
								<input type="text" name="" value="<?=$user_post_count?>" readonly class="form-control"/>
							</div>
						</div>
						<div class="form-row">
							<div class="form-group col-6">
								<label for="account number">Account number</label>
								<input type="number" name="account_number" value="<?=$post['account_number']?>" placeholder="0123456789" maxlength="10" class="form-control"/>
							</div>
							<div class="form-group col-6">
								<label for="bank">Bank</label>
								<select name="bank" class="form-control" id="selectBank">
									<option value="">Select an option</option>
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
						<div class="form-row">
							<div class="form-group col-12">
								<label for="status">Status</label>
								<select name="status" required class="form-control" id="selectStatus">
									<option value="active">Activate</option>
									<?php if((Session::get('rank') === 'admin' && $post['rank'] !== 'admin') || (Session::get('rank') === 'moderator' && $post['rank'] === 'editor')): ?>
										<option value="suspend 1 week">Suspend for 1 week</option>
										<option value="suspend 1 month">Suspend for 1 month</option>
										<option value="deleted">Delete</option>
									<?php endif; ?>
								</select>
							</div>
						</div>
						<input type="hidden" name="form_token" value="<?=Hash::hashUseHmac('sha256', get_page_name(), Session::get('_token'))?>"/>
						<div class="form-row">
							<div class="form-group col-12">
								<button type="submit" name="update_user_profile" class="btn btn-success">Update</button>
							</div>
						</div>
					</form>
				</div>

				<div class="col-12 col-sm-10 offset-sm-1 mb-4" id="userReply">
					<h2 class="text-center s-blue"><?=$post['fname']?>'s replies</h2>
					<?php $replies = $db->selectQuery('__replies', ['broadcast_id', 'reply', 'date_added'], ['WHERE' => ['user_id' => $user_id, 'seen' => 0], 'ORDER' => ['id', 'DESC']]);
					if($replies->row_count() > 0): ?>
						<?php while($data = $replies->results()): ?>
							<div class="col-12">
								<div class="card border-dark mb-3">
									<div class="card-header"><?=Datetime::setDateTime($data['date_added'])?> <span aria-hidden="true">&#124;</span> <strong class="s-black">#<?=$data['broadcast_id']?></strong></div>
									<div class="card-body text-dark">
										<p class="card-text"><?=$data['reply']?></p>
									</div>
								</div>
							</div>
						<?php endwhile; ?>
						<?php if(checkSubscription($user_id) && checkEligiblity($user_id, 'earned')): ?>
							<form class="form-inline mb-0" role="form" action="" method="post" enctype="multipart/form-data">
								<input type="hidden" name="form_token" value="<?=Hash::hashUseHmac('sha256', get_page_name(), Session::get('_token'))?>"/>
								<button type="submit" name="credit_user" class="btn btn-primary"><i class="fas fa-dollar-sign" aria-hidden="true"></i> Credit user</button>
							</form>
						<?php else: ?>
							<div class="text-center text-muted font-italic">This user has already been credited or does not have an active subscription.</div>
						<?php endif; ?>
						<form class="form-inline mb-0" role="form" action="" method="post" enctype="multipart/form-data">
							<input type="hidden" name="form_token" value="<?=Hash::hashUseHmac('sha256', get_page_name(), Session::get('_token'))?>"/>
							<button type="submit" name="mark_all_reply_read" class="btn btn-success"><i class="fas fa-check" aria-hidden="true"></i> Mark all replies as read</button>
						</form>
					<?php else: ?>
						<div class="text-center s-no-data-msg">No replies from <?=$post['fname']?></div>
					<?php endif; ?>
				</div>

				<div class="mb-4" id="userTransactions">
					<h2 class="text-center s-blue"><?=$post['fname']?>'s transaction history</h2>
					<h3>Subscription history</h3>
					<table class="table table-striped">
						<thead>
							<tr>
								<th scope="col">ID</th>
								<th scope="col">Referance</th>
								<th scope="col">Plan</th>
								<th scope="col">Subscription date</th>
								<th scope="col">Subscription end</th>
							</tr>
						</thead>
						<tbody>
						<?php
							$selectQuerySubscription = $db->query("SELECT id, reference, plan, subscription_date, subscription_end FROM __subscriptions WHERE user_id = ? ORDER BY id DESC", [$user_id]);

							while($data = $selectQuerySubscription->results()): ?>
								<tr>
									<td scope="row"><?=$data['id']?></td>
									<td><?=$data['reference']?></td>
									<td><?=$data['plan']?></td>
									<td><?=Datetime::setDateTime($data['subscription_date'])?></td>
									<td><?=Datetime::setDateTime($data['subscription_end'])?></td>
								</tr>
							<?php endwhile; ?>
						</tbody>
					</table>
					
					<h3 class="mt-5">Account history</h3>
					<table class="table table-striped">
						<thead>
							<tr>
								<th scope="col">ID</th>
								<th scope="col">Type</th>
								<th scope="col">Remark</th>
								<th scope="col">Amount</th>
								<th scope="col">Date/Time</th>
								<th scope="col">Status</th>
							</tr>
						</thead>
						<tbody id="displayData"></tbody>
					</table>
				</div>

			</div>
		</div>

		<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
		<script src="https://unpkg.com/popper.js@1.12.6/dist/umd/popper.js" integrity="sha384-fA23ZRQ3G/J53mElWqVJEGJzU0sTs+SvzG8fXVWP+kJQ1lwFAOkcUOysnlKJC33U" crossorigin="anonymous"></script>
		<script type="text/javascript" src="<?=SROOT?>assets/bootstrap-material-design-4.1.1-dist/js/bootstrap-material-design.min.js"></script>
		<script type="text/javascript" src="<?=SROOT?>assets/js/script.js?v=20200414"></script>
		<script type="text/javascript">
			optionSelected('selectBank', '<?=$post['bank']?>');
			optionSelected('selectStatus', '<?=$post['status']?>');
			$("#displayData").loaddata({data_url:'<?=SROOT?>includes/fetch-user-control-account.php', end_record_text:'No data to load'}, {'user_id':'<?=$user_id?>'});
		</script>
	</body>
</html>