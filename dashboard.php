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

use Classes\{Cookie, Database, Datetime, Hash, Router, Session, Filesystem};

$db = Database::getInstance();
$filesystem = new Filesystem();
$session = Session::startSession();

// Init variables
$_messages = $broadcast_data_array = [];
$_err = 0;

// If user session doesn't exist, then redirect to sign in.
if(!Session::exists('uid')) Router::redirect('signin.php');

// Require logic.
require_once __DIR__.'/includes/logic.php';

$user_data = $db->selectQuery('__users', ['fname', 'lname', 'uname', 'photo', 'rank', 'status', 'referral_code', 'referral_count', 'total_balance'], ['WHERE' => ['id' => Session::get('uid')]])->results();
$photo_url = (!empty($user_data['photo'])) ? IMG_CDN_URL.'photos/'.$user_data['photo'] : IMG_CDN_URL.'default/default-profile-picture-1280x1280.png';

$user_subscription_data = $db->selectQuery('__subscriptions', ['plan', 'subscription_date', 'subscription_end'], [
	'WHERE' => ['user_id' => Session::get('uid')],
	'ORDER' => ['id', 'DESC']])->results();
$user_plan = (empty($user_subscription_data['plan']) || !checkSubscription(Session::get('uid'))) ? '-' : $user_subscription_data['plan'];
$user_subscription_date = (empty($user_subscription_data['subscription_date']) || !checkSubscription(Session::get('uid'))) ? '-' : Datetime::setDateTime($user_subscription_data['subscription_date']);
$user_subscription_end = (empty($user_subscription_data['subscription_end']) || !checkSubscription(Session::get('uid'))) ? '-' : Datetime::setDateTime($user_subscription_data['subscription_end']);

$selectQueryUserPost = $db->selectQuery('__posts', ['id'], ['WHERE' => ['user_id' => Session::get('uid'), 'status' => 'approved']]);
(int)$user_post_count = $selectQueryUserPost->row_count();

$selectQueryUserComment = $db->selectQuery('__comments', ['id'], ['WHERE' => ['user_id' => Session::get('uid'), 'status' => 'approved']]);
(int)$user_comment_count = $selectQueryUserComment->row_count();

// Alert an editor when subscription expires
if(!checkSubscription(Session::get('uid')) && $user_data['rank'] == 'editor') $_messages[] = ["info" => "Your monthly subscription has expired and requires renewal to continue earning, <a href='".SROOT."subscribe.php' class='alert-link'>Renew now</a>"];

// Update profile photo
if(isset($_POST['save_photo'])){
	if(Hash::compareHashes(Hash::hashUseHmac('sha256', get_page_name(), Session::get('_token')), $_POST['form_token'])){
		// if(!Filesystem::checkFileSelect('photo')){
		// 	$_messages[] = ["warning" => "No selected file to upload."];
		// 	$_err = 1;
		// }
		// if(!Filesystem::isAllowedFileExt('photo', ['png', 'gif', 'jpg', 'jpeg'])){
		// 	$_messages[] = ["warning" => "This file is not supported for upload."];
		// 	$_err = 1;
		// }
		// if(!Filesystem::isAllowedFileSize('photo', 1048576)){
		// 	$_messages[] = ["warning" => "File size is greater than required file size of ".Filesystem::formatBytes(1048576)];
		// 	$_err = 1;
		// }
		// if(Filesystem::checkFileError('photo')){
		// 	$_messages[] = ["warning" => "This is a problem with the file you are trying to upload."];
		// 	$_err = 1;
		// }
		// if(!$_err){
		// 	if(!empty($user_data['photo'])){
		// 		delete_object(CDN_BUCKET_NAME, 'photos/'.$user_data['photo']);
		// 	}
		// 	$photo_name = Filesystem::useFileName('photo', true);
		// 	while(in_array('photos/'.$photo_name, list_objects_with_prefix(CDN_BUCKET_NAME, 'photos/'))){
		// 		$photo_name = Filesystem::useFileName('photo', true);
		// 	}
		// 	upload_object(CDN_BUCKET_NAME, 'photos/'.$photo_name, Filesystem::getFileTmpName('photo'));
		// 	$photo_url = IMG_CDN_URL.'photos/'.$photo_name;
		// 	$updateQueryPhoto = $db->updateQuery('__users', [
		// 		'photo' => $photo_name,
		// 		'last_edited_on' => Datetime::timestamp()], ['id' => Session::get('uid')]);
		// 	if(!$updateQueryPhoto->error()){
		// 		$_messages[] = ["success" => "Your profile photo was updated successfully"];
		// 	}else{
		// 		$_messages[] = ["danger" => $updateQueryPhoto->error_info()[2].": Unable to update your profile photo."];
		// 	}
		// }

		$upload_raw = $filesystem->upload('photo', true, 'uploads/photos/', ['png', 'gif', 'jpg', 'jpeg'], 1048576, 50);
		if($upload_raw['upload']){
			$photo_url = $upload_raw['file_upload_path'];
			$photo_name = explode('/', $upload_raw['file_upload_path']);
			$photo_name = end($photo_name);
		}else{
			foreach ($upload_raw['message'] as $message) {
				$_messages[] = ["warning" => $message];
			}
			$_err = 1;
		}
		if(!$_err){
			if(!empty($user_data['photo'])){
				Filesystem::delete('uploads/photos/'.$user_data['photo']);
			}
			$updateQueryPhoto = $db->updateQuery('__users', [
				'photo' => $photo_name,
				'last_edited_on' => Datetime::timestamp()], ['id' => Session::get('uid')]);
			if(!$updateQueryPhoto->error()){
				$_messages[] = ["success" => "Your profile photo was updated successfully"];
			}else{
				$_messages[] = ["danger" => $updateQueryPhoto->error_info()[2].": Unable to update your profile photo."];
			}
		}

	}else{
		$_messages[] = ["warning" => "Invalid token"];
	}
}

if(isset($_POST['send_reply'])){
	if(Hash::compareHashes(Hash::hashUseHmac('sha256', get_page_name(), Session::get('_token')), $_POST['form_token'])){
		$reply = $db->insertQuery('__replies', [
			'user_id' => Session::get('uid'),
			'broadcast_id' => sanitize_input($_POST['broadcast_id']),
			'reply' => sanitize_input($_POST['reply']),
			'seen' => 0,
			'date_added' => Datetime::timestamp()]);
		if(!$reply->error()){
			$_messages[] = ["success" => "Reply sent successfully"];
		}else{
			$_messages[] = ["danger" => $reply->error_info()[2].": Unable to send reply."];
		}
	}else{
		$_messages[] = ["warning" => "Invalid token"];
	}
}

// All messages passed through urls as GET methods
if(!empty($_GET['msg'])){
	switch($_GET['msg']){
		case 'verify':
			$_messages[] = ["info" => "This account has already been verified."];
		break;
		case 'subscribe':
			$_messages[] = ["info" => "You still have an active subscription."];
		break;
		case 'verified':
			$_messages[] = ["success" => "Nicely done! Your ".SITE_NAME." account has been verified successfully!"];
		break;
		case 'activated':
			$_messages[] = ["success" => "Your subscription has been successfully activated."];
		break;
		case 'created':
			if(isset($_GET['pid'])) $pid = $_GET['pid'];
			// $_messages[] = "Great! Your post has been created successfully. All posts need to undergo moderation before it can be either accepted or declined by an admin.";
			$_messages[] = ["success" => "Great! Your post has been created successfully and is visible on the <a href='".SROOT."' class='alert-link'>homepage</a>. Want to make changes to this post? <a href='".SROOT."edit-post.php?p=".$pid."' class='alert-link'>Edit here</a>"];
		break;
	}
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr" prefix="website: http://ogp.me/ns/website#">
	<head>
		<?php include_once __DIR__.'/includes/gtm.php'; ?>
		<title>Dashboard &#8208; <?=SITE_NAME?></title>
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
				<h2 class="text-center s-blue">Dashboard</h2>
				<div class="col-12 mb-5">
					<img src="<?=$photo_url?>" width="200" height="200" class="rounded-circle m-auto s-display-block s-pp-border-4x"/>
					<div class="text-center my-3">
						<a href="#" role="button" data-toggle="modal" data-target="#photoModal"><i class="fas fa-image" aria-hidden="true"></i> change</a>
						<span aria-hidden="true">&bull;</span>
						<a href="<?=$photo_url?>" target="_blank"><i class="fas fa-eye" aria-hidden="true"></i> view</a>
					</div>
					<h2 class="text-center my-1"><?=$user_data['fname'].' '.$user_data['lname']?></h2>
					<h3 class="text-center my-1 s-black"><?=$user_data['uname']?></h3>
					<h4 class="text-center my-1 s-blue"><?=$user_data['rank']?></h4>
					<div class="text-center text-white m-auto" style="background:#1b1b1b; width:80px; border-radius:10px;"><?=$user_data['status']?></div>
				</div>

				<div class="modal fade" id="photoModal" tabindex="-1" role="dialog" aria-labelledby="photoModalLabel" aria-hidden="true">
					<div class="modal-dialog" role="document">
						<div class="modal-content">
							<div class="modal-header">
								<h4 class="text-center">Update profile photo</h4>
								<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria=hidden="true">&times;</span></button>
							</div>
							<div class="modal-body">
								<form role="form" action="" method="post" enctype="multipart/form-data">
									<div class="form-group">
										<label for="text-muted">Profile photo</label>
										<input type="file" name="photo" accept="image/*" required class="form-control-file"/>
										<small class="form-text text-muted">Max Size: 1mb, Recommended Dimension: 100px by 100px</small>
									</div>
									<input type="hidden" name="form_token" value="<?=Hash::hashUseHmac('sha256', get_page_name(), Session::get('_token'))?>"/>
									<div class="form-group">
										<button type="submit" name="save_photo" class="btn btn-success">Upload</button>
									</div>
								</form>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-secondary" data-dismiss="modal" aria-label="Close">Close</button>
							</div>
						</div>
					</div>
				</div>

				<div class="modal fade" id="replyModal" tabindex="-1" role="dialog" aria-labelledby="replyModalLabel" aria-hidden="true">
					<div class="modal-dialog" role="document">
						<div class="modal-content">
							<div class="modal-header">
								<h4 class="text-center">Replying to broadcast <strong class="s-black" id="broadcastID"></strong></h4>
								<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria=hidden="true">&times;</span></button>
							</div>
							<div class="modal-body">
								<form role="form" action="" method="post" enctype="multipart/form-data">
									<div class="form-group">
										<textarea name="reply" required placeholder="Type here..." class="form-control"></textarea>
									</div>
									<input type="hidden" name="broadcast_id" value="" id="broadcast_id"/>
									<input type="hidden" name="form_token" value="<?=Hash::hashUseHmac('sha256', get_page_name(), Session::get('_token'))?>"/>
									<div class="form-group">
										<button type="submit" name="send_reply" class="btn btn-success">Send</button>
									</div>
									<small class="form-text text-muted font-italic">You can only reply <strong>once</strong> to a broadcast.</small>
								</form>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-secondary" data-dismiss="modal" aria-label="Close">Close</button>
							</div>
						</div>
					</div>
				</div>

				<div class="row">
					<?php $selectQueryBroadcast = $db->query('SELECT id, users, broadcast, allow_reply, expiry FROM __broadcasts WHERE expiry > ? ORDER BY id DESC', [Datetime::timestamp()]);
					while($broadcast_data = $selectQueryBroadcast->results()) $broadcast_data_array[] = $broadcast_data;
					foreach($broadcast_data_array as $broadcast_data): ?>
						<?php if(in_array(Session::get('uid'), explode(',', $broadcast_data['users']))): ?>
							<div class="col-12">
								<div class="card bg-dark mb-3">
									<div class="card-header text-white">
										<h5><i class="fas fa-broadcast-tower s-black" aria-hidden="true"></i> Broadcast <strong class="s-black">#<?=$broadcast_data['id']?></strong></h5>
										<span>Disappears: <?=Datetime::setDateTime($broadcast_data['expiry'])?></span>
									</div>
									<div class="card-body text-white s-broadcast">
										<p class="card-text"><?=$broadcast_data['broadcast']?></p>
									</div>
									<?php if($broadcast_data['allow_reply']): ?>
										<div class="card-footer bg-transparent">
											<?php $selectQueryReplies = $db->selectQuery('__replies', ['id'], ['WHERE' => ['user_id' => Session::get('uid'), 'broadcast_id' => $broadcast_data['id']]]);
											if($selectQueryReplies->row_count() <= 0): ?>
												<button type="button" data-toggle="modal" data-target="#replyModal" class="btn btn-primary" onclick="document.getElementById('broadcast_id').value = <?=$broadcast_data['id']?>; document.getElementById('broadcastID').innerText = '#<?=$broadcast_data['id']?>'">Reply to this broadcast</button>
											<?php else: ?>
												<small class="text-primary font-italic">You have replied to this broadcast.</small>
											<?php endif; ?>
										</div>
									<?php endif; ?>
								</div>
							</div>
						<?php endif; ?>
					<?php endforeach; ?>

					<div class="col-12 col-sm-4">
						<div class="card border-dark mb-3">
							<div class="card-body text-dark">
								<h5 class="card-title">Total balance</h5>
								<p class="card-text"><span aria-hidden="true">&#8358;</span><?=$user_data['total_balance']?>.00</p>
							</div>
							<div class="card-footer bg-transparent border-success">
								<a href="<?=SROOT?>withdraw.php" class="btn btn-success btn-block">Request withdrawal</a>
							</div>
						</div>
					</div>

					<div class="col-12 col-sm-4">
						<div class="card border-dark mb-3">
							<div class="card-body text-dark">
								<h5 class="card-title">Balance</h5>
								<p class="card-text"><span aria-hidden="true">&#8358;</span><?=$_BALANCE?>.00</p>
							</div>
						</div>
					</div>

					<div class="col-12 col-sm-4">
						<div class="card border-dark mb-3">
							<div class="card-body text-dark">
								<h5 class="card-title">Bonus</h5>
								<p class="card-text"><span aria-hidden="true">&#8358;</span><?=$_BONUS?>.00</p>
							</div>
						</div>
					</div>

					<div class="col-12 col-sm-4">
						<div class="card border-dark mb-3">
							<div class="card-body text-dark">
								<h5 class="card-title">Referrals</h5>
								<p class="card-text"><?=$user_data['referral_count']?></p>
							</div>
						</div>
					</div>

					<div class="col-12 col-sm-8">
						<div class="card border-dark mb-3">
							<div class="card-body text-dark">
								<h5 class="card-title">Referral link</h5>
								<p class="card-text text-muted">
									When you copy and share your <strong>referral link</strong>, you earn an extra percentage from the plan the referred user selects.
									<input type="text" value="<?=SITE_URL.SROOT.'?rc='.$user_data['referral_code']?>" readonly class="form-control" id="referralLinkField"/>
								</p>
							</div>
							<div class="card-footer bg-transparent border-primary">
								<button class="btn btn-default" data-clipboard-target="#referralLinkField" id="copyBtn"><i class="fas fa-copy" aria-hidden="true"></i> Copy</button>
							</div>
						</div>
					</div>

					<div class="col-12 col-sm-4">
						<div class="card border-dark mb-3">
							<div class="card-body text-dark">
								<h5 class="card-title">Current plan</h5>
								<p class="card-text"><?=$user_plan?></p>
							</div>
							<div class="card-footer bg-transparent border-success">
								<a href="<?=SROOT?>subscribe.php" class="btn btn-success btn-block">Upgrade/Update subscription</a>
							</div>
						</div>
					</div>

					<div class="col-12 col-sm-4">
						<div class="card border-dark mb-3">
							<div class="card-body text-dark">
								<h5 class="card-title">Subscription date</h5>
								<p class="card-text"><?=$user_subscription_date?></p>
							</div>
						</div>
					</div>

					<div class="col-12 col-sm-4">
						<div class="card border-dark mb-3">
							<div class="card-body text-dark">
								<h5 class="card-title">Subscription ends</h5>
								<p class="card-text"><?=$user_subscription_end?></p>
							</div>
						</div>
					</div>

					<div class="col-12 col-sm-6">
						<div class="card border-dark mb-3">
							<div class="card-body text-dark">
								<h5 class="card-title">Posts</h5>
								<p class="card-text"><?=$user_post_count?></p>
							</div>
						</div>
					</div>

					<div class="col-12 col-sm-6">
						<div class="card border-dark mb-3">
							<div class="card-body text-dark">
								<h5 class="card-title">Comments</h5>
								<p class="card-text"><?=$user_comment_count?></p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
		<script src="https://unpkg.com/popper.js@1.12.6/dist/umd/popper.js" integrity="sha384-fA23ZRQ3G/J53mElWqVJEGJzU0sTs+SvzG8fXVWP+kJQ1lwFAOkcUOysnlKJC33U" crossorigin="anonymous"></script>
		<script type="text/javascript" src="<?=SROOT?>assets/bootstrap-material-design-4.1.1-dist/js/bootstrap-material-design.min.js"></script>
		<script type="text/javascript" src="<?=SROOT?>assets/clipboard.js/clipboard.min.js"></script>
		<script type="text/javascript" src="<?=SROOT?>assets/js/script.js?v=20200414"></script>
		<script type="text/javascript">
			var clipboard = new ClipboardJS("#copyBtn");
		</script>
	</body>
</html>