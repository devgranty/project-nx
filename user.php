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

use Classes\{Database, Router, Session};

$db = Database::getInstance();
$session = Session::startSession();

if(!empty($_GET['u'])){
	$username = sanitize_input($_GET['u']);
	
	// If user session doesn't exist, then redirect to sign in.
	if(!Session::exists('uid')) Router::redirect('signin.php?next='.urlencode(SROOT.get_page_name().'.php?u='.$username));

	$selectQuery = $db->selectQuery('__users', ['id', 'fname', 'lname', 'uname', 'photo', 'bio', 'rank', 'status'], ['WHERE' => ['uname' => $username]]);
	if($selectQuery->row_count() <= 0){
		Router::redirect('404.php');
	}
	$data = $selectQuery->results();
	if(!empty($data['photo'])){
		$photo = IMG_CDN_URL.'photos/'.$data['photo'];
	}else{
		$photo = IMG_CDN_URL.'default/default-profile-picture-1280x1280.png';
	}

	$user_comment_count = $db->selectQuery('__comments', ['id'], ['WHERE' => ['user_id' => $data['id'], 'status' => 'approved']])->row_count();

	$user_post_count = $db->selectQuery('__posts', ['id'], ['WHERE' => ['user_id' => $data['id'], 'status' => 'approved']])->row_count();
}else{
	Router::redirect('404.php');
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr" prefix="website: http://ogp.me/ns/website#">
	<head>
		<?php include_once __DIR__.'/includes/gtm.php'; ?>
		<title><?=$data['uname']?> &#8208; <?=SITE_NAME?></title>
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
			<?php include_once __DIR__.'/includes/nav.php'; ?>

			<div class="container mt-5">

				<?php if($data['status'] == 'active'): ?>
				
					<section class="s-section-area">
						<div class="row">
							<div class="col-12 col-sm-6">
								<img src="<?=$photo?>" width="200" height="200" class="rounded-circle m-auto s-display-block s-pp-border-4x"/>
								<h2 class="text-center my-1"><?=$data['fname']." ".$data['lname']?></h2>
								<h3 class="text-center my-1 s-black"><?=$data['uname']?></h3>
								<h4 class="text-center my-1 s-blue"><?=$data['rank']?></h4>
							</div>
							<div class="col-12 col-sm-6 d-inline-flex">
								<div class="col-6"><h3><?=$user_post_count?></h3>Post(s)</div>
								<div class="col-6"><h3><?=$user_comment_count?></h3>comment(s)</div>
							</div>
						</div>
						<div class="card my-4">
							<div class="card-body"><?=$data['bio']?></div>
						</div>
					</section>

					<section class="s-section-area">
						<h4>POSTS BY <?=$data['uname']?></h4>
						<div class="row" id="displayData"></div>
					</section>

				<?php else: ?>

					<div class="jumbotron">
						<div class="container">
							<h1><i class="fas fa-exclamation-triangle s-warning-alert" aria-hidden="true"></i> This page isn't available</h1>
							<hr class="my-3">
							<p class="lead">The link you followed may be broken, or the page may have been removed.</p>
						</div>
					</div>

				<?php endif; ?>
			</div>
		</div>

		<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
		<script src="https://unpkg.com/popper.js@1.12.6/dist/umd/popper.js" integrity="sha384-fA23ZRQ3G/J53mElWqVJEGJzU0sTs+SvzG8fXVWP+kJQ1lwFAOkcUOysnlKJC33U" crossorigin="anonymous"></script>
		<script type="text/javascript" src="<?=SROOT?>assets/bootstrap-material-design-4.1.1-dist/js/bootstrap-material-design.min.js"></script>
		<script type="text/javascript" src="<?=SROOT?>assets/js/script.js?v=20200414"></script>
		<script type="text/javascript">
			$("#displayData").loaddata({data_url:'<?=SROOT?>includes/fetch-user-posts.php', end_record_text:'No posts to load'}, {'user_id':'<?=$data['id']?>'});
		</script>
	</body>
</html>