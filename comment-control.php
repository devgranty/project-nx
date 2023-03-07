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

if(!empty($_GET['c'])){
    $comment_id = sanitize_int($_GET['c']);
    $selectQuery = $db->query('SELECT __comments.id, __comments.comment, __comments.date_added, __comments.status, __users.uname, __posts.title FROM __comments JOIN __users ON __comments.user_id = __users.id JOIN __posts ON __comments.post_id = __posts.id WHERE __comments.id = ?', [$comment_id]);
    if($selectQuery->row_count() <= 0){
        Router::redirect('404.php');
    }
	$data = $selectQuery->results();
	
    if(isset($_POST['update_comment'])){
		if(Hash::compareHashes(Hash::hashUseHmac('sha256', get_page_name(), Session::get('_token')), $_POST['form_token'])){
			$updateQuery = $db->updateQuery('__comments', [
				'status' => sanitize_input($_POST['status'])], ['id' => $comment_id]);
			if(!$updateQuery->error()){
				$_messages[] = ["success" => "Comment with id:$comment_id was updated successfully"];
			}else{
				$_messages[] = ["danger" => $updateQuery->error_info()[2].": Unable to update comment."];
			}
		}else{
			$_messages[] = ["warning" => "Invalid token"];
		}
    }
}else{
	Router::redirect('404.php');
}

$post = post_values(['comment' => $data['comment'], 'date_added' => Datetime::setDateTime($data['date_added']), 'status' => $data['status'], 'uname' => $data['uname'], 'title' => $data['title']], 'update_comment');
?>

<!DOCTYPE html>
<html lang="en" dir="ltr" prefix="website: http://ogp.me/ns/website#">
	<head>
		<?php include_once __DIR__.'/includes/gtm.php'; ?>
		<title>Comment control &#8208; <?=SITE_NAME?></title>
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
					<h2 class="text-center s-blue">Comment ID:<?=$comment_id?></h2>
					<form role="form" action="" method="post" enctype="multipart/form-data">
						<div class="form-row">
							<div class="form-group col-6">
								<label for="username">Username</label>
								<input type="text" name="uname" value="<?=$post['uname']?>" readonly class="form-control"/>
							</div>
							<div class="form-group col-6">
								<label for="title">Post title</label>
								<input type="text" name="title" value="<?=$post['title']?>" readonly class="form-control"/>
							</div>
						</div>
						<div class="form-row">
							<div class="form-group col-12">
								<label class="sr-only" for="comment">Comment</label>
								<textarea name="comment" readonly class="form-control" style="resize:vertical;"><?=$post['comment']?></textarea>
							</div>
						</div>
						<div class="form-row">
							<div class="form-group col-12">
								<label for="date added">Date added</label>
								<input type="text" name="date_added" value="<?=$post['date_added']?>" readonly class="form-control"/>
							</div>
						</div>
						<div class="form-row">
							<div class="form-group col-12">
								<label for="status">Status</label>
								<select name="status" required class="form-control" id="selectStatus">
									<option value="approved">Approve</option>
									<option value="disapproved">Disapprove</option>
								</select>
							</div>
						</div>
						<input type="hidden" name="form_token" value="<?=Hash::hashUseHmac('sha256', get_page_name(), Session::get('_token'))?>"/>
						<div class="form-row">
							<div class="form-group col-12">
								<button type="submit" name="update_comment" class="btn btn-success">Update</button>
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
		<script type="text/javascript">
			optionSelected('selectStatus', '<?=$post['status']?>');
		</script>
	</body>
</html>