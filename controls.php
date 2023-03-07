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

// Init variables
$_messages = [];

// If user session doesn't exist, then redirect to sign in.
if(!Session::exists('uid')) Router::redirect('signin.php');
// Check if user is an editor/moderator
if(Session::get('rank') == 'editor' || Session::get('rank') == 'moderator') Router::redirect('403.php');

if(isset($_POST['save_controls'])){
	if(Hash::compareHashes(Hash::hashUseHmac('sha256', get_page_name(), Session::get('_token')), $_POST['form_token'])){
		if($db->selectQuery('__settings', ['id'])->row_count() <= 0){
			$insertQuery = $db->insertQuery('__settings', [
				'referral_percentage' => sanitize_int($_POST['referral_percentage']),
				'number_pcc' => sanitize_int($_POST['number_pcc']),
				'number_pvc' => sanitize_int($_POST['number_pvc']),
				'number_cpc' => sanitize_int($_POST['number_cpc']),
				'number_words_pcc' => sanitize_int($_POST['number_words_pcc']),
				'tester_earn' => sanitize_int($_POST['tester_earn']),
				'basic_earn' => sanitize_int($_POST['basic_earn']),
				'bronze_earn' => sanitize_int($_POST['bronze_earn']),
				'silver_earn' => sanitize_int($_POST['silver_earn']),
				'emerald_earn' => sanitize_int($_POST['emerald_earn']),
				'jasper_earn' => sanitize_int($_POST['jasper_earn']),
				'ruby_earn' => sanitize_int($_POST['ruby_earn']),
				'gold_earn' => sanitize_int($_POST['gold_earn'])
			]);
			if(!$insertQuery->error()){
				$_messages[] = ["success" => "Settings successfully added."];
			}else{
				$_messages[] = ["danger" => $insertQuery->error_info()[2].": Unable to add settings."];
			}
		}else{
			$updateQuery = $db->updateQuery('__settings', [
				'referral_percentage' => sanitize_int($_POST['referral_percentage']),
				'number_pcc' => sanitize_int($_POST['number_pcc']),
				'number_pvc' => sanitize_int($_POST['number_pvc']),
				'number_cpc' => sanitize_int($_POST['number_cpc']),
				'number_words_pcc' => sanitize_int($_POST['number_words_pcc']),
				'tester_earn' => sanitize_int($_POST['tester_earn']),
				'basic_earn' => sanitize_int($_POST['basic_earn']),
				'bronze_earn' => sanitize_int($_POST['bronze_earn']),
				'silver_earn' => sanitize_int($_POST['silver_earn']),
				'emerald_earn' => sanitize_int($_POST['emerald_earn']),
				'jasper_earn' => sanitize_int($_POST['jasper_earn']),
				'ruby_earn' => sanitize_int($_POST['ruby_earn']),
				'gold_earn' => sanitize_int($_POST['gold_earn'])
			], ['id' => 1]);
			if(!$updateQuery->error()){
				$_messages[] = ["success" => "Settings successfully updated."];
			}else{
				$_messages[] = ["danger" => $updateQuery->error_info()[2].": Unable to update settings."];
			}
		}
	}else{
		$_messages[] = ["warning" => "Invalid token"];
	}
}

$post = post_values(['referral_percentage' => fetchSettings('referral_percentage'), 'number_pcc' => fetchSettings('number_pcc'), 'number_pvc' => fetchSettings('number_pvc'), 'number_cpc' => fetchSettings('number_cpc'), 'number_words_pcc' => fetchSettings('number_words_pcc'), 'tester_earn' => fetchSettings('tester_earn'), 'basic_earn' => fetchSettings('basic_earn'), 'bronze_earn' => fetchSettings('bronze_earn'), 'silver_earn' => fetchSettings('silver_earn'), 'emerald_earn' => fetchSettings('emerald_earn'), 'jasper_earn' => fetchSettings('jasper_earn'), 'ruby_earn' => fetchSettings('ruby_earn'), 'gold_earn' => fetchSettings('gold_earn')], 'save_controls');
?>

<!DOCTYPE html>
<html lang="en" dir="ltr" prefix="website: http://ogp.me/ns/website#">
	<head>
		<?php include_once __DIR__.'/includes/gtm.php'; ?>
		<title>Controls &#8208; <?=SITE_NAME?></title>
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
					<h2 class="text-center s-blue">Controls</h2>
					<p class="text-muted text-center">All fields are required</p>
					<form role="form" action="" method="post" enctype="multipart/form-data" id="createForm">
						<div class="form-row">
							<div class="form-group col-12">
								<label for="referral percentage">Referral percentage (%)</label>
								<input type="number" name="referral_percentage" value="<?=$post['referral_percentage']?>" required placeholder="10" class="form-control"/>
							</div>
						</div>
						<div class="form-row">
							<div class="form-group col-12">
								<label for="number of times for earn by post creation">Number of times for earn by post creation</label>
								<input type="number" name="number_pcc" value="<?=$post['number_pcc']?>" required placeholder="2" class="form-control"/>
							</div>
						</div>
						<div class="form-row">
							<div class="form-group col-12">
								<label for="number of times for earn by post view">Number of times for earn by post view</label>
								<input type="number" name="number_pvc" value="<?=$post['number_pvc']?>" required placeholder="29" class="form-control"/>
							</div>
						</div>
						<div class="form-row">
							<div class="form-group col-12">
								<label for="number of times for earn by comment post">Number of times for earn by comment post</label>
								<input type="number" name="number_cpc" value="<?=$post['number_cpc']?>" required placeholder="5" class="form-control"/>
							</div>
						</div>
						<div class="form-row">
							<div class="form-group col-12">
								<label for="number of words for post creation earning">Number of words for post creation earning</label>
								<input type="number" name="number_words_pcc" value="<?=$post['number_words_pcc']?>" required placeholder="250" class="form-control"/>
							</div>
						</div>
						<div class="form-row">
							<div class="form-group col-12 col-sm-3">
								<label for="tester daily earn amount">Tester daily earn amount</label>
								<input type="number" name="tester_earn" value="<?=$post['tester_earn']?>" required placeholder="170" class="form-control"/>
							</div>
							<div class="form-group col-12 col-sm-3">
								<label for="basic daily earn amount">Basic daily earn amount</label>
								<input type="number" name="basic_earn" value="<?=$post['basic_earn']?>" required placeholder="284" class="form-control"/>
							</div>
							<div class="form-group col-12 col-sm-3">
								<label for="bronze daily earn amount">Bronze daily earn amount</label>
								<input type="number" name="bronze_earn" value="<?=$post['bronze_earn']?>" required placeholder="454" class="form-control"/>
							</div>
							<div class="form-group col-12 col-sm-3">
								<label for="silver daily earn amount">Silver daily earn amount</label>
								<input type="number" name="silver_earn" value="<?=$post['silver_earn']?>" required placeholder="737" class="form-control"/>
							</div>
						</div>
						<div class="form-row">
							<div class="form-group col-12 col-sm-3">
								<label for="emerald daily earn amount">Emerald daily earn amount</label>
								<input type="number" name="emerald_earn" value="<?=$post['emerald_earn']?>" required placeholder="1190" class="form-control"/>
							</div>
							<div class="form-group col-12 col-sm-3">
								<label for="jasper daily earn amount">Jasper daily earn amount</label>
								<input type="number" name="jasper_earn" value="<?=$post['jasper_earn']?>" required placeholder="1984" class="form-control"/>
							</div>
							<div class="form-group col-12 col-sm-3">
								<label for="ruby daily earn amount">Ruby daily earn amount</label>
								<input type="number" name="ruby_earn" value="<?=$post['ruby_earn']?>" required placeholder="3286" class="form-control"/>
							</div>
							<div class="form-group col-12 col-sm-3">
								<label for="gold daily earn amount">Gold daily earn amount</label>
								<input type="number" name="gold_earn" value="<?=$post['gold_earn']?>" required placeholder="5834" class="form-control"/>
							</div>
						</div>
						<input type="hidden" name="form_token" value="<?=Hash::hashUseHmac('sha256', get_page_name(), Session::get('_token'))?>"/>
						<div class="form-row">
							<div class="form-group col-12">
								<button type="submit" name="save_controls" class="btn btn-success btn-lg">Save</button>
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
	</body>
</html>