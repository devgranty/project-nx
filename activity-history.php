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

use Classes\{Database, Router, Hash, Session};

$db = Database::getInstance();
$session = Session::startSession();

// Init variables
$_messages = [];
$post_active = $comment_active = '';

// If user session doesn't exist, then redirect to sign in.
if(!Session::exists('uid')) Router::redirect('signin.php?next='.urlencode(SROOT.get_page_name().'.php'));

// Require logic.
require_once __DIR__.'/includes/logic.php';

if(!empty($_GET['event_type'])){
	switch($_GET['event_type']){
		case 'post':
			$event_type = "post";
		break;
		case 'comment':
			$event_type = "comment";
		break;
		default:
			$event_type = "post";
		break;
	}
}else{
	$event_type = "post";
}

if($event_type == 'post'){
	$post_active = 'active';
}elseif($event_type == 'comment'){
	$comment_active = 'active';
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr" prefix="website: http://ogp.me/ns/website#">
	<head>
		<?php include_once __DIR__.'/includes/gtm.php'; ?>
		<title>Activity history &#8208; <?=SITE_NAME?></title>
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
				<h2 class="text-center s-blue">Activity history</h2>
				<a href="<?=SROOT.get_page_name().'.php?event_type=post'?>" class="btn btn-primary <?=$post_active?>">Post history</a>
				<a href="<?=SROOT.get_page_name().'.php?event_type=comment'?>" class="btn btn-success <?=$comment_active?>">Comment history</a>
				<div class="row" id="displayData"></div>
			</div>
		</div>
		
		<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
		<script src="https://unpkg.com/popper.js@1.12.6/dist/umd/popper.js" integrity="sha384-fA23ZRQ3G/J53mElWqVJEGJzU0sTs+SvzG8fXVWP+kJQ1lwFAOkcUOysnlKJC33U" crossorigin="anonymous"></script>
		<script type="text/javascript" src="<?=SROOT?>assets/bootstrap-material-design-4.1.1-dist/js/bootstrap-material-design.min.js"></script>
		<script type="text/javascript" src="<?=SROOT?>assets/js/script.js?v=20200414"></script>
		<script type="text/javascript">
			$("#displayData").loaddata({data_url:'<?=SROOT?>includes/fetch-activity-history.php', end_record_text:'No data to load'}, {'event_type':'<?=$event_type?>'});
		</script>
	</body>
</html>