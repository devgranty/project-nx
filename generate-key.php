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

use Classes\{Database, Hash, Router, Session, Str};

$db = Database::getInstance();
$session = Session::startSession();

// Init variable
$_messages = [];

// If user session doesn't exist, then redirect to sign in.
if(!Session::exists('uid')) Router::redirect('signin.php');
// Check if user is an editor/moderator
if(Session::get('rank') == 'editor' || Session::get('rank') == 'moderator') Router::redirect('403.php');

if(isset($_POST['generate_key'])){
	if(Hash::compareHashes(Hash::hashUseHmac('sha256', get_page_name(), Session::get('_token')), $_POST['form_token'])){
		$gen_random_str = Str::randomStr(12);
		$gen_random_str = strtoupper($gen_random_str);
		$sub_key_count = $db->selectQuery('__sub_keys', ['id'], ['WHERE' => ['sub_key' => $gen_random_str]])->row_count();
		while($sub_key_count > 0){
			$gen_random_str = Str::randomStr(12);
			$gen_random_str = strtoupper($gen_random_str);
		}
		$sub_key_insert = $db->insertQuery('__sub_keys',[
			'sub_key' => $gen_random_str,
			'plan' => sanitize_input($_POST['plan'])
		]);
		if(!$sub_key_insert->error()){
			$_messages[] = ["success" => "Subscription key for plan $_POST[plan] - key been successfully generated, <strong>M-$gen_random_str</strong>. NB: Subscription keys are deleted when they are used."];
		}else{
			$_messages[] = ["danger" => $sub_key_insert->error_info()[2].": Unable to generate subscription key."];
		}
	}else{
		$_messages[] = ["warning" => "Invalid token"];
	}
}

if(isset($_POST['delete_sub_key'])){
	if(Hash::compareHashes(Hash::hashUseHmac('sha256', get_page_name(), Session::get('_token')), $_POST['form_token'])){
		$sub_key_id = sanitize_int($_POST['k']);
		$sub_key_delete = $db->deleteQuery('__sub_keys', ['id' => $sub_key_id]);
		if(!$sub_key_delete->error()){
			$_messages[] = ["success" => "Subscription key with id:".$sub_key_id." successfully deleted."];
		}else{
			$_messages[] = ["danger" => $sub_key_delete->error_info()[2].": Unable to delete subscription key."];
		}
	}else{
		$_messages[] = ["warning" => "Invalid token"];
	}
}

$post = post_values(['plan' => ''], 'generate_key');
?>

<!DOCTYPE html>
<html lang="en" dir="ltr" prefix="website: http://ogp.me/ns/website#">
	<head>
		<?php include_once __DIR__.'/includes/gtm.php'; ?>
		<title>Generate key &#8208; <?=SITE_NAME?></title>
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
					<h2 class="text-center s-blue">Generate a subscription key</h2>

					<form role="form" action="" method="post" enctype="multipart/form-data">
						<div class="form-row">
							<div class="form-group col-12">
								<p class="text-muted">Select a plan below to generate a 12-digit subscription key:</p>
								<select name="plan" required class="form-control" id="selectPlan">
									<option value="" disabled="disabled">Select a plan</option>
									<option value="tester">Tester</option>
									<option value="basic">Basic</option>
									<option value="bronze">Bronze</option>
									<option value="silver">Silver</option>
									<option value="emerald">Emerald</option>
									<option value="jasper">Jasper</option>
									<option value="ruby">Ruby</option>
									<option value="gold">Gold</option>
								</select>
							</div>
						</div>
						<input type="hidden" name="form_token" value="<?=Hash::hashUseHmac('sha256', get_page_name(), Session::get('_token'))?>"/>
						<div class="form-row">
							<div class="form-group col-12">
								<button type="submit" name="generate_key" class="btn btn-success btn-lg">Generate key</button>
							</div>
						</div>
					</form>
					
				</div>

				<h2 class="text-center s-blue">Last 10 generated unused key list</h2>
				<table class="table table-striped">
					<thead>
						<tr>
							<th scope="col">ID</th>
							<th scope="col">Sub key</th>
							<th scope="col">Plan</th>
							<th scope="col">Action</th>
						</tr>
					</thead>
					<tbody>
					<?php
						$selectQuerySub_key = $db->query("SELECT id, sub_key, plan FROM __sub_keys ORDER BY id DESC LIMIT 0, 10");
						while($data = $selectQuerySub_key->results()): ?>
							<tr>
								<td scope="row"><?=$data['id']?></td>
								<td id="subKeyId<?=$data['id']?>">M-<?=$data['sub_key']?></td>
								<td><?=$data['plan']?></td>
								<td><form class="form-inline mb-0" role="form" action="" method="post" enctype="multipart/form-data">
									<input type="hidden" name="k" value="<?=$data['id']?>"/>
									<input type="hidden" name="form_token" value="<?=Hash::hashUseHmac('sha256', get_page_name(), Session::get('_token'))?>"/>
									<button type="button" data-clipboard-target="#subKeyId<?=$data['id']?>" id="copyBtn" class="btn btn-primary"><i class="fas fa-copy" aria-hidden="true"></i> Copy</button>
									<button type="submit" name="delete_sub_key" class="btn btn-danger"><i class="fas fa-trash-alt" aria-hidden="true"></i> Delete</button>
								</form></td>
							</tr>
						<?php endwhile; ?>
					</tbody>
				</table>

			</div>
		</div>

		<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
		<script src="https://unpkg.com/popper.js@1.12.6/dist/umd/popper.js" integrity="sha384-fA23ZRQ3G/J53mElWqVJEGJzU0sTs+SvzG8fXVWP+kJQ1lwFAOkcUOysnlKJC33U" crossorigin="anonymous"></script>
		<script type="text/javascript" src="<?=SROOT?>assets/bootstrap-material-design-4.1.1-dist/js/bootstrap-material-design.min.js"></script>
        <script type="text/javascript" src="<?=SROOT?>assets/clipboard.js/clipboard.min.js"></script>
		<script type="text/javascript" src="<?=SROOT?>assets/js/script.js?v=20200414"></script>
        <script type="text/javascript">
			optionSelected('selectPlan', '<?=$post['plan']?>');
			var clipboard = new ClipboardJS("#copyBtn");
		</script>
	</body>
</html>