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

if(!empty($_GET['p'])){
	$post_slug = sanitize_input($_GET['p']);
	$selectQueryPost = $db->query('SELECT __posts.id, __posts.user_id, __posts.title, __posts.category, __posts.section, __posts.thumbnail, __posts.article, __posts.source, __posts.date_added, __users.uname, __users.photo FROM __posts JOIN __users ON __posts.user_id = __users.id WHERE __posts.slug = ? AND __posts.status = ?', [$post_slug, 'approved']);
	if($selectQueryPost->row_count() <= 0){
		Router::redirect('404.php');
	}
	$data = $selectQueryPost->results();

	switch($data['section']){
		case 'pinned':
			$section = '&bull; <i class="fas fa-thumbtack" aria-hidden="true" data-toggle="tooltip" title="Pinned post"></i>';
		break;
		case 'trending':
			$section = '&bull; <i class="fas fa-fire-alt" aria-hidden="true" data-toggle="tooltip" title="Trending post"></i>';
		break;
		default:
			$section = '';
		break;
	}

	if(!empty($data['thumbnail'])){
		$thumbnail = IMG_CDN_URL.'images/'.$data['thumbnail'];
	}else{
		$thumbnail = IMG_CDN_URL.'default/default-thumbnail-960x540.png';
	}
	if(!empty($data['photo'])){
		$photo = IMG_CDN_URL.'photos/'.$data['photo'];
	}else{
		$photo = IMG_CDN_URL.'default/default-profile-picture-1280x1280.png';
	}

	earnByPostView($data['id']);

	$page_url = SITE_URL.SROOT.get_page_name().'.php?p='.$post_slug;

	if(isset($_POST['post_comment'])){
		if(Hash::compareHashes(Hash::hashUseHmac('sha256', get_page_name(), Session::get('_token')), $_POST['form_token'])){
			$insertQueryComment = $db->insertQuery('__comments', [
				'post_id' => $data['id'],
				'user_id' => Session::get('uid'),
				'comment' => sanitize_input($_POST['comment']),
				'date_added' => Datetime::timestamp(),
				'status' => 'approved'
			]);
			if(!$insertQueryComment->error()){
				earnByCommentPost($data['id']);
				$_messages[] = ["success" => "Comment posted successfully"];
			}else{
				$_messages[] = ["danger" => $insertQueryComment->error_info()[2].": Unable to post comment."];
			}
		}else{
			$_messages[] = ["warning" => "Invalid token"];
		}
	}

	if(isset($_POST['delete_comment'])){
		if(Hash::compareHashes(Hash::hashUseHmac('sha256', get_page_name(), Session::get('_token')), $_POST['form_token'])){
			if(Session::get('uid') === sanitize_int($_POST['user_id'])){
				$comment_id = sanitize_int($_POST['comment_id']);
				$comment_update = $db->updateQuery('__comments', ['status' => 'deleted'], ['id' => $comment_id, 'user_id' => Session::get('uid')]);
				if(!$comment_update->error()){
					$_messages[] = ["success" => "Comment successfully deleted."];
				}else{
					$_messages[] = ["danger" => $comment_update->error_info()[2].": Unable to delete comment."];
				}
			}else{
				$_messages[] = ["danger" => "Error: Cannot complete requested action."];
			}
		}else{
			$_messages[] = ["warning" => "Invalid token"];
		}
	}

}else{
	Router::redirect('404.php');	
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr" prefix="website: http://ogp.me/ns/website#">
	<head>
		<?php include_once __DIR__.'/includes/gtm.php'; ?>
		<title><?=$data['title']?> &#8208; <?=SITE_NAME?></title>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="theme-color" content="#000000">
		<link rel="icon" href="<?=SROOT?>favicon.png" sizes="19x19" type="image/png">
		<link rel="apple-touch-icon" href="<?=SROOT?>assets/icons/icon.png" type="image/png">
		<link rel="stylesheet" type="text/css" href="<?=SROOT?>assets/css/style.css?v=20200407">
		<meta name="robots" content="index">
		<meta name="description" content="<?=$data['title']?>">
		<link rel="canonical" href="<?=$page_url?>">
		<meta property="og:title" content="<?=$data['title']?> &#8208; <?=SITE_NAME?>">
		<meta property="og:type" content="website">
		<meta property="og:image" content="<?=$thumbnail?>">
		<meta property="og:image:type" content="">
		<meta property="og:image:width" content="">
		<meta property="og:image:height" content="">
		<meta property="og:url" content="<?=$page_url?>">
		<meta property="og:description" content="<?=$data['title']?>">
		<meta property="og:locale" content="en_US">
		<meta property="og:site_name" content="<?=SITE_NAME?>">
		<meta name="twitter:card" content="summary_large_image">
		<meta name="twitter:site" content="">
		<meta name="twitter:title" content="<?=$data['title']?> &#8208; <?=SITE_NAME?>">
		<meta name="twitter:description" content="<?=$data['title']?>">
		<meta name="twitter:image" content="<?=$thumbnail?>">
		<style>
			article ul{list-style-position:inside;}
			article ol{list-style-position:inside;}
			article img{width:100%;}
		</style>
		<script type="application/ld+json">
		{
		"@context": "https://schema.org",
		"@type": "Article",
		"headline": "<?=$data['title']?>",
		"image": "<?=$thumbnail?>",  
		"author": {
			"@type": "Person",
			"name": "<?=$data['uname']?>"
		},  
		"publisher": {
			"@type": "Organization",
			"name": "<?=SITE_NAME?>",
			"logo": {
			"@type": "ImageObject",
			"url": "<?=SITE_URL.SROOT?>assets/icons/icon.png",
			"width": 146,
			"height": 146
			}
		},
		"datePublished": "<?=Datetime::setDateTimeFormat('Y-m-d', $data['date_added'])?>"
		}
		</script>
	</head>
	<body>
		<?php include_once __DIR__.'/includes/gtm-ns.php'; ?>
		<div class="min-vh-100">
			<?php include_once __DIR__.'/includes/nav.php'; 
				alert_messages($_messages);
			?>

			<div class="container">
				<section class="s-section-area">
					<h1 class="col-12 s-forum-title" style="font-family:'Playfair Display', serif;"><?=$data['title']?></h1>
					<div class="col-12 mt-4">
						<ul class="list-unstyled s-forum-citation">
							<li><img class="rounded-circle ml-3 s-display-block s-pp-border-2x" src="<?=$photo?>" width="50" height="50"/></li>
							<li class="list-inline-item mr-0"><a href="<?=SROOT?>user.php?u=<?=$data['uname']?>" data-toggle="tooltip" title="<?=$data['uname']?>"><?=$data['uname']?></a></li>
							<li class="list-inline-item mr-0"> <span aria-hidden="true">&bull;</span> <?=Datetime::timeTranslate($data['date_added'])?></li>
							<li class="list-inline-item mr-0"> <span aria-hidden="true">&bull;</span> <a href="<?=SROOT?>category.php?type=<?=$data['category']?>" data-toggle="tooltip" title="<?=$data['category']?>"><?=$data['category']?></a></li>
							<li class="list-inline-item mr-0"><?=$section?></li>
						</ul>

						<?php if(Session::exists('uid')): ?>
							<?php if(Session::get('uid') === $data['user_id']): ?>
								<div class="float-left s-dropdown">
									<button class="btn btn-primary active"><i class="fas fa-ellipsis-v"></i> More</button>
									<div class="s-dropdown-content">
										<ul class="list-unstyled">
											<li><a href="<?=SROOT?>edit-post.php?p=<?=$data['id']?>"><i class="fas fa-edit"></i> Edit post</a></li>
										</ul>
									</div>
								</div>
							<?php endif; ?>
						<?php endif; ?>

						<ul class="list-unstyled text-right s-forum-share-plate">
							<li class="list-inline-item"><a href="https://www.facebook.com/sharer/sharer.php?u=<?=$page_url?>" onclick="window.open(this.href);return false;"><svg width="29" height="29" xmlns="http://www.w3.org/2000/svg"><path d="M23.2 5H5.8a.8.8 0 0 0-.8.8V23.2c0 .44.35.8.8.8h9.3v-7.13h-2.38V13.9h2.38v-2.38c0-2.45 1.55-3.66 3.74-3.66 1.05 0 1.95.08 2.2.11v2.57h-1.5c-1.2 0-1.48.57-1.48 1.4v1.96h2.97l-.6 2.97h-2.37l.05 7.12h5.1a.8.8 0 0 0 .79-.8V5.8a.8.8 0 0 0-.8-.79"></path></svg></a></li>
							<li class="list-inline-item"><a href="https://twitter.com/intent/tweet?url=<?=$page_url?>&text=<?=$data['title']?>" onclick="window.open(this.href);return false;"><svg width="29" height="29" xmlns="http://www.w3.org/2000/svg"><path d="M22.05 7.54a4.47 4.47 0 0 0-3.3-1.46 4.53 4.53 0 0 0-4.53 4.53c0 .35.04.7.08 1.05A12.9 12.9 0 0 1 5 6.89a5.1 5.1 0 0 0-.65 2.26c.03 1.6.83 2.99 2.02 3.79a4.3 4.3 0 0 1-2.02-.57v.08a4.55 4.55 0 0 0 3.63 4.44c-.4.08-.8.13-1.21.16l-.81-.08a4.54 4.54 0 0 0 4.2 3.15 9.56 9.56 0 0 1-5.66 1.94l-1.05-.08c2 1.27 4.38 2.02 6.94 2.02 8.3 0 12.86-6.9 12.84-12.85.02-.24 0-.43 0-.65a8.68 8.68 0 0 0 2.26-2.34c-.82.38-1.7.62-2.6.72a4.37 4.37 0 0 0 1.95-2.51c-.84.53-1.81.9-2.83 1.13z"></path></svg></a></li>
							<li class="list-inline-item mr-0"><button type="button" id="copyBtn" class="btn m-0 p-0" data-clipboard-text="<?=$page_url?>"><svg width="29" height="29" xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd"><path d="M14.851 11.923c-.179-.641-.521-1.246-1.025-1.749-1.562-1.562-4.095-1.563-5.657 0l-4.998 4.998c-1.562 1.563-1.563 4.095 0 5.657 1.562 1.563 4.096 1.561 5.656 0l3.842-3.841.333.009c.404 0 .802-.04 1.189-.117l-4.657 4.656c-.975.976-2.255 1.464-3.535 1.464-1.28 0-2.56-.488-3.535-1.464-1.952-1.951-1.952-5.12 0-7.071l4.998-4.998c.975-.976 2.256-1.464 3.536-1.464 1.279 0 2.56.488 3.535 1.464.493.493.861 1.063 1.105 1.672l-.787.784zm-5.703.147c.178.643.521 1.25 1.026 1.756 1.562 1.563 4.096 1.561 5.656 0l4.999-4.998c1.563-1.562 1.563-4.095 0-5.657-1.562-1.562-4.095-1.563-5.657 0l-3.841 3.841-.333-.009c-.404 0-.802.04-1.189.117l4.656-4.656c.975-.976 2.256-1.464 3.536-1.464 1.279 0 2.56.488 3.535 1.464 1.951 1.951 1.951 5.119 0 7.071l-4.999 4.998c-.975.976-2.255 1.464-3.535 1.464-1.28 0-2.56-.488-3.535-1.464-.494-.495-.863-1.067-1.107-1.678l.788-.785z"/></svg></button></li>
						</ul>
					</div>
				</section>

				<section class="s-section-area">
					<?php if(!empty($data['source'])): ?>
						<h4 class="text-center mb-3"><i class="fas fa-globe" aria-hidden="true"></i> Source: <a href="<?=$data['source']?>" target="_blank" rel="external"><?= $source = (!empty($data['source'])) ? @parse_url($data['source'])['host'] : ''; ?></a></h4>
					<?php endif; ?>

					<img src="<?=$thumbnail?>" class="mb-4" style="width:100%;"/>
					<article style="font-size:18px; font-family:arial;"><?=$data['article']?></article>
				</section>

				<section class="s-section-area">
					<?php $selectQueryRelatedPost = $db->query('SELECT __posts.id, __posts.slug, __posts.title, __posts.section, __posts.thumbnail, __posts.article, __posts.date_added, __users.uname, __users.photo FROM __posts JOIN __users ON __posts.user_id = __users.id WHERE __posts.category = ? AND __posts.section = ? AND __posts.status = ? AND __posts.id != ? ORDER BY RAND() LIMIT 6', [$data['category'], $data['section'], 'approved', $data['id']]); ?>

					<h4 id="related-post">RELATED POSTS &bull; <?=$selectQueryRelatedPost->row_count()?></h4>
					<div class="row">
						<?php if($selectQueryRelatedPost->row_count() > 0):

							while($related_post_data = $selectQueryRelatedPost->results()):
								if(!empty($related_post_data['thumbnail'])){
									$thumbnail = IMG_CDN_URL.'images/'.$related_post_data['thumbnail'];
								}else{
									$thumbnail = IMG_CDN_URL.'default/default-thumbnail-960x540.png';
								}
								if(!empty($related_post_data['photo'])){
									$photo = IMG_CDN_URL.'photos/'.$related_post_data['photo'];
								}else{
									$photo = IMG_CDN_URL.'default/default-profile-picture-1280x1280.png';
								}
								switch($related_post_data['section']){
									case 'pinned':
										$related_post_section = '<span class="s-bull" aria-hidden="true">&bull;</span> <i class="fas fa-thumbtack" aria-hidden="true" data-toggle="tooltip" title="Pinned post"></i>';
									break;
									case 'trending':
										$related_post_section = '<span class="s-bull" aria-hidden="true">&bull;</span> <i class="fas fa-fire-alt" aria-hidden="true" data-toggle="tooltip" title="Trending post"></i>';
									break;
									default:
										$related_post_section = '';
									break;
								}?>
								<div class="col-12 col-sm-6 col-md-4 mb-2">
									<div class="card">
										<img class="card-img-top" src="<?=$thumbnail?>" alt="<?=$related_post_data['title']?>">
										<div class="card-body">
											<h5 class="card-title s-blue">
												<a href="<?=SROOT?>forum.php?p=<?=$related_post_data['slug']?>" data-toggle="tooltip" title="<?=$related_post_data['title']?>" class="s-blue"><?=$related_post_data['title']?></a>
											</h5>
											<p class="card-text s-height-45px s-clamp-text"><?=substr(strip_tags($related_post_data['article']), 0, 250)?></p>
											<p class="card-text">
												<img src="<?=$photo?>" width="25" height="25" class="rounded-circle s-pp-border-2x"/>
												<small><a href="<?=SROOT?>user.php?u=<?=$related_post_data['uname']?>" data-toggle="tooltip" title="<?=$related_post_data['uname']?>" class="s-blue"><?=$related_post_data['uname']?></a></small>
												<span class="s-bull" aria-hidden="true">&bull;</span> <small class="text-muted"><?=Datetime::timeTranslate($related_post_data['date_added'])?></small>
												<?=$related_post_section?>
											</p>
										</div>
									</div>
								</div>
								
							<?php endwhile; ?>

						<?php else: ?>

							<div class="s-no-data-msg">There are no related posts available</div>

						<?php endif; ?>
					</div>
				</section>

				<section class="s-section-area">
					<?php $selectQueryComment = $db->query('SELECT __comments.comment, __comments.date_added, __users.uname, __users.photo FROM __comments JOIN __users ON __comments.user_id = __users.id WHERE __comments.post_id = ? AND __comments.status = ? ORDER BY __comments.id DESC', [$data['id'], 'approved']); ?>
					
					<h4 id="comments">COMMENTS &bull; <?=$selectQueryComment->row_count()?></h4>
					<div class="row">
						<?php if(Session::exists('uid')): ?>
							<div class="modal fade" id="confirmCommentDeleteModal" tabindex="-1" role="dialog" aria-labelledby="confirmCommentDeleteModalLabel" aria-hidden="true">
								<div class="modal-dialog" role="document">
									<div class="modal-content">
										<div class="modal-header">
											<h4 class="text-center">Are you sure you want to delete this comment?</h4>
											<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria=hidden="true">&times;</span></button>
										</div>
										<div class="modal-body">
											<form role="form" action="" method="post" enctype="multipart/form-data">
												<input type="hidden" name="comment_id" value="" id="comment_id"/>
												<input type="hidden" name="user_id" value="" id="user_id"/>
												<input type="hidden" name="form_token" value="<?=Hash::hashUseHmac('sha256', get_page_name(), Session::get('_token'))?>"/>
												<div class="form-row">
													<div class="form-group col-6">
														<button type="submit" name="delete_comment" class="btn btn-danger btn-block active"><i class="fas fa-trash-alt"></i> Delete</button>
													</div>
													<div class="form-group col-6">
														<button type="button" class="btn btn-primary btn-block" data-dismiss="modal" aria-label="Cancel">Cancel</button>
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
							<div class="col-12">
								<div class="card border-dark mb-3">
									<div class="card-header text-primary">Join the discussion</div>
									<div class="card-body">
										<form role="form" action="" method="post" enctype="multipart/form-data">
											<div class="form-group">
												<textarea name="comment" placeholder="Add a comment..." required maxlength="1000" rows="2" title="Comment field cannot be empty" class="form-control" style="resize:vertical;"></textarea>
											</div>
											<input type="hidden" name="form_token" value="<?=Hash::hashUseHmac('sha256', get_page_name(), Session::get('_token'))?>"/>
											<div class="form-group">
												<button type="submit" name="post_comment" class="btn btn-primary">Post</button>
											</div>
										</form>
									</div>
									<div class="card-footer bg-transparent text-muted">Commenting as <strong><?=Session::get('uname')?></strong></div>
								</div>
							</div>
						<?php else: ?>

							<div class="s-no-data-msg" style="margin-bottom:20px;">You need to be signed in to comment, <a href="<?=SROOT?>signin.php?next=<?=urlencode(SROOT.get_page_name().'.php?p='.$post_slug.'#comments')?>">Sign in?</a></div>

						<?php endif; ?>

						<div class="col-12" id="displayData"></div>
					</div>
				</section>
			</div>
		</div>

		<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
		<script src="https://unpkg.com/popper.js@1.12.6/dist/umd/popper.js" integrity="sha384-fA23ZRQ3G/J53mElWqVJEGJzU0sTs+SvzG8fXVWP+kJQ1lwFAOkcUOysnlKJC33U" crossorigin="anonymous"></script>
		<script type="text/javascript" src="<?=SROOT?>assets/bootstrap-material-design-4.1.1-dist/js/bootstrap-material-design.min.js"></script>
		<script type="text/javascript" src="<?=SROOT?>assets/clipboard.js/clipboard.min.js"></script>
		<script type="text/javascript" src="<?=SROOT?>assets/js/script.js?v=20200414"></script>
		<script type="text/javascript">
			var clipboard = new ClipboardJS("#copyBtn");
			$("#displayData").loaddata({data_url:'<?=SROOT?>includes/fetch-forum-comments.php', end_record_text:'No comments to load'}, {'post_id':'<?=$data['id']?>'});
		</script>
	</body>
</html>