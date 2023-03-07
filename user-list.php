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

use Classes\{Database, Hash, Router, Session};

$db = Database::getInstance();
$session = Session::startSession();

// Init Variables
$_messages = [];
$username = '';

// If user session doesn't exist, then redirect to sign in.
if(!Session::exists('uid')) Router::redirect('signin.php');
// Check if user is an editor
if(Session::get('rank') == 'editor') Router::redirect('403.php');

if(!empty($_GET['u'])){
	$username = sanitize_input($_GET['u']);
}

if(!empty($_GET['msg'])){
	switch($_GET['msg']){
		case 'invalid_token':
			$_messages[] = ["warning" => "Invalid token"];
		break;
		case 'no_selected_user':
			$_messages[] = ["info" => "No selected user, select a user and try again"];
		break;
		case 'broadcast_successful':
			$_messages[] = ["success" => "Broadcast successfully sent"];
		break;
		case 'broadcast_unsuccessful':
			if(isset($_GET['error'])) $error = $_GET['error'];
			$_messages[] = ["danger" => "$error Unable to send broadcast."];
		break;
	}
}

if(Session::exists('broadcast_users')) Session::remove('broadcast_users');
if(Session::exists('broadcast_excluded_users')) Session::remove('broadcast_excluded_users');
?>


<!DOCTYPE html>
<html lang="en" dir="ltr" prefix="website: http://ogp.me/ns/website#">
	<head>
		<?php include_once __DIR__.'/includes/gtm.php'; ?>
		<title>Users &#8208; <?=SITE_NAME?></title>
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
				<h2 class="text-center s-blue">Users</h2>
				<form role="form" action="" method="get" enctype="multipart/form-data">
					<div class="form-row">
						<div class="form-group col-8">
							<input type="search" name="u" value="<?=$username?>" placeholder="Enter a username to search..." required autocomplete="off" class="form-control"/>
						</div>
						<div class="form-group col-4">
							<button type="submit" name="" autocomplete="off" class="btn btn-primary">Search</button>
							<a href="<?=SROOT.get_page_name().'.php'?>" class="btn btn-default"><span aria-hidden="true">&times;</span> Clear search</a>
						</div>
					</div>
				</form>

				<form class="form-inline" role="form" action="<?=SROOT?>broadcast.php" method="post" enctype="multipart/form-data">
					<button type="submit" name="send_broadcast_to_selected" class="btn btn-primary"><i class="fas fa-broadcast-tower" aria-hidden="true"></i> Broadcast</button>
					<button type="submit" name="send_broadcast_to_all" class="btn btn-warning"><i class="fas fa-broadcast-tower" aria-hidden="true"></i> Broadcast all</button>
					<button type="submit" name="send_broadcast_to_except" class="btn btn-danger"><i class="fas fa-broadcast-tower" aria-hidden="true"></i> Broadcast all except</button>
					<input type="hidden" name="form_token" value="<?=Hash::hashUseHmac('sha256', 'broadcast', Session::get('_token'))?>"/>
					<table class="table table-striped">
						<thead>
							<tr>
								<th scope="col">ID</th>
								<th scope="col">Username</th>
								<th scope="col">Email</th>
								<th scope="col">Phone</th>
								<th scope="col">Rank</th>
								<th scope="col">Date</th>
								<th scope="col">Status</th>
								<th scope="col">Action</th>
								<th scope="col">Select</th>
							</tr>
						</thead>
						<tbody id="displayData"></tbody>
					</table>
				</form>
			</div>
		</div>

		<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
		<script src="https://unpkg.com/popper.js@1.12.6/dist/umd/popper.js" integrity="sha384-fA23ZRQ3G/J53mElWqVJEGJzU0sTs+SvzG8fXVWP+kJQ1lwFAOkcUOysnlKJC33U" crossorigin="anonymous"></script>
		<script type="text/javascript" src="<?=SROOT?>assets/bootstrap-material-design-4.1.1-dist/js/bootstrap-material-design.min.js"></script>
		<script type="text/javascript" src="<?=SROOT?>assets/js/script.js?v=20200414"></script>
		<script type="text/javascript">
			$("#displayData").loaddata({data_url:'<?=SROOT?>includes/fetch-user-list.php', end_record_text:'No data to load'}, {'username':'<?=$username?>'});
		</script>
	</body>
</html>