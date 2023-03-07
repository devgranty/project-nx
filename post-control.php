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

use Classes\{Database, Datetime, Filesystem, Hash, Router, Session, Str, Validate};

$db = Database::getInstance();
$purifier = new HTMLPurifier();
$session = Session::startSession();

// Init variable
$_messages = [];
$_err = 0;

// If user session doesn't exist, then redirect to sign in.
if(!Session::exists('uid')) Router::redirect('signin.php');
// Check if user is an editor
if(Session::get('rank') == 'editor') Router::redirect('403.php');

if(!empty($_GET['p'])){
	$post_id = sanitize_int($_GET['p']);

	$selectQueryComment = $db->selectQuery('__comments', ['id'], ['WHERE' => ['post_id' => $post_id, 'status' => 'approved']]);
	$comment_count = $selectQueryComment->row_count();

	$selectQuery = $db->query('SELECT __posts.id, __posts.title, __posts.category, __posts.section, __posts.thumbnail, __posts.article, __posts.source, __posts.date_added, __posts.status, __users.uname FROM __posts JOIN __users ON __posts.user_id = __users.id WHERE __posts.id = ?', [$post_id]);
	if($selectQuery->row_count() <= 0){
		Router::redirect('404.php');
	}
	$data = $selectQuery->results();

	if(!empty($data['thumbnail'])){
		$thumbnail = IMG_CDN_URL.'images/'.$data['thumbnail'];
	}else{
		$thumbnail = IMG_CDN_URL.'default/default-thumbnail-960x540.png';
	}

	if(isset($_POST['update_article'])){
		if(Hash::compareHashes(Hash::hashUseHmac('sha256', get_page_name(), Session::get('_token')), $_POST['form_token'])){
			if(Validate::checkDuplicates($_POST['title'], '__posts', 'title') > 0 && $_POST['title'] !== $data['title']){
				$_messages[] = ["warning" => "A post with this title already exists, unable to update post."];
				$_err = 1;
			}
			if(!$_err){
				$updateQuery = $db->updateQuery('__posts', [
					'slug' => Str::createSlug($_POST['title']),
					'title' => sanitize_input($_POST['title']),
					'category' => sanitize_input($_POST['category']),
					'section' => sanitize_input($_POST['section']),
					'article' => $purifier->purify($_POST['article']),
					'source' => sanitize_input($_POST['source']),
					'status' => sanitize_input($_POST['status'])], ['id' => $post_id]);
				if(!$updateQuery->error()){
					$_messages[] = ["success" => "Post with id:$post_id was updated successfully"];
				}else{
					$_messages[] = ["danger" => $updateQuery->error_info()[2].": Unable to update post."];
				}
			}
		}else{
			$_messages[] = ["warning" => "Invalid token"];
		}
	}

	if(isset($_POST['update_thumbnail'])){
		if(Hash::compareHashes(Hash::hashUseHmac('sha256', get_page_name(), Session::get('_token')), $_POST['form_token'])){
			if(!empty($_FILES['thumbnail']['name'])){
				// if(!Filesystem::checkFileSelect('thumbnail')){
				// 	$_messages[] = ["warning" => "No selected file to upload [Thumbnail]."];
				// 	$_err = 1;
				// }
				// if(!Filesystem::isAllowedFileExt('thumbnail', ['png', 'gif', 'jpg', 'jpeg'])){
				// 	$_messages[] = ["warning" => "This file is not supported for upload [Thumbnail]."];
				// 	$_err = 1;
				// }
				// if(!Filesystem::isAllowedFileSize('thumbnail', 1048576)){
				// 	$_messages[] = ["warning" => "File size is greater than required file size of ".Filesystem::formatBytes(1048576)." [Thumbnail]."];
				// 	$_err = 1;
				// }
				// if(Filesystem::checkFileError('thumbnail')){
				// 	$_messages[] = ["warning" => "This is a problem with the file you are trying to upload [Thumbnail]."];
				// 	$_err = 1;
				// }
				// if(!$_err){
				// 	if(!empty($data['thumbnail'])){
				// 		delete_object(CDN_BUCKET_NAME, 'images/'.$data['thumbnail']);
				// 	}
				// 	$thumbnail_name = Filesystem::useFileName('thumbnail', true, 50);
				// 	while(in_array('images/'.$thumbnail_name, list_objects_with_prefix(CDN_BUCKET_NAME, 'images/'))){
				// 		$thumbnail_name = Filesystem::useFileName('thumbnail', true, 50);
				// 	}
				// 	upload_object(CDN_BUCKET_NAME, 'images/'.$thumbnail_name, Filesystem::getFileTmpName('thumbnail'));

				// 	$updateQuery = $db->updateQuery('__posts', [
				// 		'thumbnail' => $thumbnail_name], ['id' => $post_id]);
				// 	if(!$updateQuery->error()){
				// 		$_messages[] = ["success" => "Thumbnail updated successfully."];
				// 	}else{
				// 		$_messages[] = ["danger" => $updateQuery->error_info()[2].": Unable to update thumbnail."];
				// 	}
				// }

				$upload_raw = $filesystem->upload('thumbnail', true, 'uploads/images/', ['png', 'gif', 'jpg', 'jpeg'], 1048576, 50);
				if($upload_raw['upload']){
					$thumbnail_name = explode('/', $upload_raw['file_upload_path']);
					$thumbnail_name = end($thumbnail_name);
				}else{
					foreach ($upload_raw['message'] as $message) {
						$_messages[] = ["warning" => $message];
					}
					$_err = 1;
				}
				if(!$_err){
					if(!empty($data['thumbnail'])){
						Filesystem::delete('uploads/images/'.$data['thumbnail']);
					}
					$updateQuery = $db->updateQuery('__posts', [
						'thumbnail' => $thumbnail_name], ['id' => $post_id]);
					if(!$updateQuery->error()){
						$_messages[] = ["success" => "Thumbnail updated successfully."];
					}else{
						$_messages[] = ["danger" => $updateQuery->error_info()[2].": Unable to update thumbnail."];
					}
				}
			}
		}else{
			$_messages[] = ["warning" => "Invalid token"];
		}
	}
}else{
	Router::redirect('404.php');
}

$post = post_values(['title' => $data['title'], 'category' => $data['category'], 'section' => $data['section'], 'article' => $data['article'], 'source' => $data['source'], 'date_added' => Datetime::setDateTime($data['date_added']), 'status' => $data['status'], 'uname' => $data['uname']], 'update_article');
?>

<!DOCTYPE html>
<html lang="en" dir="ltr" prefix="website: http://ogp.me/ns/website#">
	<head>
		<?php include_once __DIR__.'/includes/gtm.php'; ?>
		<title>Post control &#8208; <?=SITE_NAME?></title>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="theme-color" content="#000000">
		<link rel="icon" href="<?=SROOT?>favicon.png" sizes="19x19" type="image/png">
		<link rel="apple-touch-icon" href="<?=SROOT?>assets/icons/icon.png" type="image/png">
		<link rel="stylesheet" type="text/css" href="<?=SROOT?>assets/css/style.css?v=20200407">
		<link rel="stylesheet" type="text/css" href="<?=SROOT?>assets/quill-1.3.6/css/quill.snow.css">
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
					<h2 class="text-center s-blue">Post ID:<?=$post_id?></h2>
					<form role="form" action="" method="post" enctype="multipart/form-data" id="createForm">
						<div class="form-row">
							<div class="form-group col-12">
								<label for="title">Source</label>
								<input type="url" name="source" value="<?=$post['source']?>" placeholder="Add http://" class="form-control" maxlength="1000" title="Must include a scheme (http:// or https://)"/>
								<small class="form-text text-muted">We frown greatly at <strong>plagiarism</strong>. If this is a copied work please add a <strong>reference URL</strong> to that work.</small>
							</div>
						</div>
						<div class="form-row">
							<div class="form-group col-6">
								<label for="username">Username</label>
								<input type="text" name="uname" value="<?=$post['uname']?>" required placeholder="" readonly class="form-control"/>
							</div>
							<div class="form-group col-6">
								<label for="title">Title</label>
								<input type="text" name="title" value="<?=$post['title']?>" required placeholder="" class="form-control"/>
							</div>
						</div>
						<div class="form-row">
							<div class="form-group col-12">
								<img src="<?=$thumbnail?>" class="mb-4" style="width:100%;"/>
							</div>
						</div>
						<div class="form-group">
							<div id="editor" style="height:250px;"><?=$post['article']?></div>
						</div>
						<div class="form-row">
							<div class="form-group col-6">
								<label for="category">Category</label>
								<select name="category" required class="form-control" id="selectCategory">
									<option value="" disabled="disabled">Select an option</option>
									<option value="business">Business</option>
									<option value="sports">Sports</option>
									<option value="entertainment">Entertainment</option>
									<option value="politics">Politics</option>
									<option value="health">Health</option>
									<option value="general">General</option>
									<option value="social">Social</option>
									<option value="crime">Crime</option>
									<option value="legal">Legal</option>
									<option value="lifestyle">Lifestyle</option>
									<option value="international">International</option>
									<option value="art">Art</option>
									<option value="tech">Tech</option>
									<option value="education">education</option>
									<option value="auto">Auto</option>
									<option value="national">National</option>
								</select>
							</div>
							<div class="form-group col-6">
								<label for="date added">Date added</label>
								<input type="text" name="date_added" value="<?=$post['date_added']?>" required placeholder="" readonly class="form-control"/>
							</div>
						</div>
						<div class="form-row">
							<div class="form-group col-6">
								<label for="section">Section</label>
								<select name="section" required class="form-control" id="selectSection">
									<option value="" disabled="disabled">Select an option</option>
									<option value="normal">Normal</option>
									<option value="pinned">Pinned</option>
									<option value="trending">Trending</option>
								</select>
							</div>
							<div class="form-group col-6">
								<label for="comment count">Comment count</label>
								<input type="text" name="" value="<?=$comment_count?>" readonly class="form-control"/>
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
						<input type="hidden" name="article" value="" id="article"/>
						<input type="hidden" name="form_token" value="<?=Hash::hashUseHmac('sha256', get_page_name(), Session::get('_token'))?>"/>
						<div class="form-row">
							<div class="form-group col-6">
								<button type="submit" name="update_article" class="btn btn-success btn-block">Update article</button>
							</div>
							<div class="form-group col-6">
								<button type="button" data-toggle="modal" data-target="#thumbnailModal" class="btn btn-primary btn-block">Change thumbnail</button>
							</div>
						</div>
					</form>
				</div>
				
				<div class="modal fade" id="thumbnailModal" tabindex="-1" role="dialog" aria-labelledby="thumbnailModalLabel" aria-hidden="true">
					<div class="modal-dialog" role="document">
						<div class="modal-content">
							<div class="modal-header">
								<h4 class="text-center">Change thumbnail</h4>
								<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria=hidden="true">&times;</span></button>
							</div>
							<div class="modal-body">
								<p class="text-muted text-center">All fields are required</p>
								<form role="form" action="" method="post" enctype="multipart/form-data">
									<div class="form-row">
										<div class="form-group col-12">
											<label for="thumbnail">Thumbnail</label>
											<input type="file" name="thumbnail" accept="image/*" required class="form-control-file"/>
											<small class="form-text text-muted">Max Size: 1mb, Recommended Dimension: 320px by 180px</small>
										</div>
									</div>
									<input type="hidden" name="form_token" value="<?=Hash::hashUseHmac('sha256', get_page_name(), Session::get('_token'))?>"/>
									<div class="form-row">
										<div class="form-group col-12">
											<input type="submit" value="Update thumbnail" name="update_thumbnail" class="btn btn-success"/>
										</div>
									</div>
								</form>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-secondary" data-dismiss="modal" aria-label="Close">Close</button>
							</div>
						</div>
					</div>
				</div>

			</div>
		</div>

		<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
		<script src="https://unpkg.com/popper.js@1.12.6/dist/umd/popper.js" integrity="sha384-fA23ZRQ3G/J53mElWqVJEGJzU0sTs+SvzG8fXVWP+kJQ1lwFAOkcUOysnlKJC33U" crossorigin="anonymous"></script>
		<script type="text/javascript" src="<?=SROOT?>assets/bootstrap-material-design-4.1.1-dist/js/bootstrap-material-design.min.js"></script>
		<script type="text/javascript" src="<?=SROOT?>assets/quill-1.3.6/js/quill.min.js"></script>
		<script type="text/javascript" src="<?=SROOT?>assets/js/script.js?v=20200414"></script>
		<script type="text/javascript">
			var toolbarOptions = [['bold', 'italic', 'underline', 'strike'], ['link', 'blockquote', 'code-block'], [{ 'list': 'ordered'}, { 'list': 'bullet' }], [{ 'header': [1, 2, 3, 4, 5, 6, false] }], ['clean'], ['image'], [{'indent': '-1'}, {'indent': '+1'}]];
			var options = {
				debug: false,
				modules: {
					toolbar: {
						container: toolbarOptions,
						handlers: {
							image: imageHandler
						}
					},
				},
				placeholder: 'Type here...',
				theme: 'snow'
			};
			var quill = new Quill('#editor', options);
			// console.log('logging: ', quill);
			var form = document.querySelector('#createForm');
			form.onsubmit = function(){
				var article = document.querySelector('#article');
				article.value = quill.root.innerHTML;
				if(quill.root.innerText.length <= 1){
					alert("Editor cannot be empty");
					return false;
				}
			}
			function imageHandler(){
				var range = this.quill.getSelection();
				var value = prompt("Enter image url:", "http://");
				if(value){
					this.quill.insertEmbed(range.index, 'image', value, Quill.sources.USER);
				}
			}
			optionSelected('selectCategory', '<?=$post['category']?>');
			optionSelected('selectSection', '<?=$post['section']?>');
			optionSelected('selectStatus', '<?=$post['status']?>');
		</script>
	</body>
</html>