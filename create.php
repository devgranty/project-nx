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

use Classes\{Database, Datetime, Hash, Router, Session, Str, Filesystem, Validate};

$db = Database::getInstance();
$filesystem = new Filesystem();
$purifier = new HTMLPurifier();
$session = Session::startSession();

// Init variables
$_messages = [];
$_err = 0;

// If user session doesn't exist, then redirect to sign in.
if(!Session::exists('uid')) Router::redirect('signin.php?next='.urlencode(SROOT.get_page_name().'.php'));

// Require logic.
require_once __DIR__.'/includes/logic.php';

if(isset($_POST['create_post'])){
	if(Hash::compareHashes(Hash::hashUseHmac('sha256', get_page_name(), Session::get('_token')), $_POST['form_token'])){
		if(Validate::checkDuplicates($_POST['title'], '__posts', 'title') > 0){
			$_messages[] = ["warning" => "A post with this title already exists, unable to create post."];
			$_err = 1;
		}

		if(!$_err){
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
				// 	$thumbnail_name = Filesystem::useFileName('thumbnail', true, 50);
				// 	while(in_array('images/'.$thumbnail_name, list_objects_with_prefix(CDN_BUCKET_NAME, 'images/'))){
				// 		$thumbnail_name = Filesystem::useFileName('thumbnail', true, 50);
				// 	}
				// 	upload_object(CDN_BUCKET_NAME, 'images/'.$thumbnail_name, Filesystem::getFileTmpName('thumbnail'));
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
			}
		}

		if(!$_err){
			$insertQuery = $db->insertQuery('__posts', [
				'slug' => Str::createSlug($_POST['title']),
				'user_id' => Session::get('uid'),
				'title' => sanitize_input($_POST['title']),
				'category' => sanitize_input($_POST['category']),
				'section' => 'normal',
				'thumbnail' => $thumbnail_name,
				'article' => $purifier->purify($_POST['article']),
				'source' => sanitize_input($_POST['source']),
				'date_added' => Datetime::timestamp(),
				'status' => 'approved'
			]);
			if(!$insertQuery->error()){
				$insertQueryLastInsertId = $db->last_insert_id();
				if(count(explode(' ', strip_tags($_POST['article']))) >= fetchSettings('number_words_pcc')){
					earnByPostCreation();
				}
				Router::redirect('dashboard.php?msg=created&pid='.$insertQueryLastInsertId);
			}else{
				$_messages[] = ["danger" => $insertQuery->error_info()[2].": Unable to create post."];
			}
		}
	}else{
		$_messages[] = ["warning" => "Invalid token"];
	}
}

$post = post_values(['title' => '', 'category' => '', 'article' => '', 'source' => ''], 'create_post');
?>

<!DOCTYPE html>
<html lang="en" dir="ltr" prefix="website: http://ogp.me/ns/website#">
	<head>
		<?php include_once __DIR__.'/includes/gtm.php'; ?>
		<title>Create &#8208; <?=SITE_NAME?></title>
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
					<h2 class="text-center s-blue">Create post</h2>
					<p class="text-muted text-center">All fields marked <span class="text-danger">*</span> are required</p>
					<form role="form" action="" method="post" enctype="multipart/form-data" id="createForm">
						<div class="form-row">
							<div class="form-group col-12">
								<label for="title">Source</label>
								<input type="url" name="source" value="<?=$post['source']?>" placeholder="Add http://" class="form-control" maxlength="1000" title="Must include a scheme (http:// or https://)"/>
								<small class="form-text text-muted">We frown greatly at <strong>plagiarism</strong>. If this is a copied work please add a <strong>reference URL</strong> to that work.</small>
							</div>
						</div>
						<div class="form-row">
							<div class="form-group col-12 col-sm-6">
								<label for="title">Title <span class="text-danger">*</span></label>
								<input type="text" name="title" value="<?=$post['title']?>" required placeholder="Title" class="form-control" maxlength="250"/>
							</div>
							<div class="form-group col-12 col-sm-6">
								<label for="category">Category <span class="text-danger">*</span></label>
								<select name="category" required class="form-control" id="selectCategory">
									<option value="" disabled="disabled">Select a category for this post</option>
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
						</div>
						<div class="form-row">
							<div class="form-group col-12">
								<label for="thumbnail">Thumbnail <span class="text-danger">*</span></label>
								<input type="file" name="thumbnail" accept="image/*" required class="form-control-file"/>
								<small class="form-text text-muted">Max Size: 1mb, Recommended Dimension: 320px by 180px</small>
							</div>
						</div>
						<div class="form-row">
							<div class="form-group col-12">
								<div id="editor" style="height:250px;"><?=$post['article']?></div>
							</div>
						</div>
						<input type="hidden" name="article" value="" id="article"/>
						<input type="hidden" name="form_token" value="<?=Hash::hashUseHmac('sha256', get_page_name(), Session::get('_token'))?>"/>
						<div class="form-row">
							<div class="form-group col-12">
								<button type="submit" name="create_post" class="btn btn-primary btn-block">Create</button>
							</div>
						</div>
					</form>
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
		</script>
	</body>
</html>