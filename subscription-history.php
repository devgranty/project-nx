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

// If user session doesn't exist, then redirect to sign in.
if(!Session::exists('uid')) Router::redirect('signin.php?next='.urlencode(SROOT.get_page_name().'.php'));

// Require logic.
require_once __DIR__.'/includes/logic.php';
?>

<!DOCTYPE html>
<html lang="en" dir="ltr" prefix="website: http://ogp.me/ns/website#">
	<head>
		<?php include_once __DIR__.'/includes/gtm.php'; ?>
		<title>Subscription history &#8208; <?=SITE_NAME?></title>
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
				<h2 class="text-center s-blue">Subscription history</h2>
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
					<tbody id="displayData">
					</tbody>
				</table>
			</div>
		</div>

		<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
		<script src="https://unpkg.com/popper.js@1.12.6/dist/umd/popper.js" integrity="sha384-fA23ZRQ3G/J53mElWqVJEGJzU0sTs+SvzG8fXVWP+kJQ1lwFAOkcUOysnlKJC33U" crossorigin="anonymous"></script>
		<script type="text/javascript" src="<?=SROOT?>assets/bootstrap-material-design-4.1.1-dist/js/bootstrap-material-design.min.js"></script>
		<script type="text/javascript" src="<?=SROOT?>assets/js/script.js?v=20200414"></script>
		<script type="text/javascript">
			$("#displayData").loaddata({data_url:'<?=SROOT?>includes/fetch-subscription-history.php', end_record_text:'No data to load'});
		</script>
	</body>
</html>