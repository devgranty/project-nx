<?php
/**
 * ---------------------------------------
 * LOAD UP APPLICATION
 * ---------------------------------------
 * Let us load up all helpers, classes
 * and application configuration.
 * Feels good to take a nap now!
 */
require_once __DIR__.'bootstrap/app.php';

use Classes\{Cookie, Database, Datetime, Session, Router};

$db = Database::getInstance();
$session = Session::startSession();

if(!empty($_GET['rc'])){
	$referral_code = sanitize_input($_GET['rc']);
	$expiry = 60*60*24*2;
	Cookie::set('_rc', $referral_code, $expiry);
	Router::redirect("signup.php?utm_source=Social&utm_medium=via_referrer&utm_campaign=ref");
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr" prefix="website: http://ogp.me/ns/website#">
	<head>
		<?php include_once __DIR__.'/includes/gtm.php'; ?>
		<title>Home &#8208; <?=SITE_NAME?></title>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="theme-color" content="#000000">
		<link rel="icon" href="<?=SROOT?>favicon.png" sizes="19x19" type="image/png">
		<link rel="apple-touch-icon" href="<?=SROOT?>assets/icons/icon.png" type="image/png">
		<link rel="stylesheet" type="text/css" href="<?=SROOT?>assets/css/style.css?v=20200407">
		<meta name="robots" content="index">
		<meta name="description" content="">
		<link rel="canonical" href="<?=SITE_URL.SROOT?>">
		<meta property="og:title" content="Home &#8208; <?=SITE_NAME?>">
		<meta property="og:type" content="website">
		<meta property="og:image" content="<?=SITE_URL.SROOT?>assets/icons/icon.png">
		<meta property="og:image:type" content="image/png">
		<meta property="og:image:width" content="146">
		<meta property="og:image:height" content="146">
		<meta property="og:url" content="<?=SITE_URL.SROOT?>">
		<meta property="og:description" content="">
		<meta property="og:locale" content="en_US">
		<meta property="og:site_name" content="<?=SITE_NAME?>">
		<meta name="twitter:card" content="summary">
		<meta name="twitter:site" content="">
		<meta name="twitter:title" content="Home &#8208; <?=SITE_NAME?>">
		<meta name="twitter:description" content="">
		<meta name="twitter:image" content="<?=SITE_URL.SROOT?>assets/icons/icon.png">
	</head>
	<body>
		<?php include_once __DIR__.'/includes/gtm-ns.php'; ?>
		<div class="min-vh-100">
			<?php include_once __DIR__.'/includes/nav.php'; ?>
			
			<div class="container">

				<section class="s-section-area" id="pinned">
					<h2 class="s-section-area-header">Pinned posts</h2>
					<div class="row">
						<?php $selectQueryPinned = $db->query('SELECT __posts.id, __posts.slug, __posts.title, __posts.thumbnail, __posts.article, __posts.user_id, __posts.date_added, __users.uname, __users.photo FROM __posts JOIN __users ON __posts.user_id = __users.id WHERE __posts.section = ? AND __posts.status = ? ORDER BY __posts.id DESC', ['pinned', 'approved']);
						
						if($selectQueryPinned->row_count() > 0):

							while($data = $selectQueryPinned->results()):
								if(!empty($data['thumbnail'])){
									$thumbnail = IMG_CDN_URL.'images/'.$data['thumbnail'];
								}else{
									$thumbnail = IMG_CDN_URL.'default/default-thumbnail-960x540.png';
								}
								if(!empty($data['photo'])){
									$photo = IMG_CDN_URL.'photos/'.$data['photo'];
								}else{
									$photo = IMG_CDN_URL.'default/default-profile-picture-1280x1280.png';
								}?>
								<div class="col-12 col-sm-6 col-md-4 mb-2">
									<div class="card">
										<img class="card-img-top" src="<?=$thumbnail?>" alt="<?=$data['title']?>">
										<div class="card-body">
											<h5 class="card-title s-blue">
												<a href="<?=SROOT?>forum.php?p=<?=$data['slug']?>" data-toggle="tooltip" title="<?=$data['title']?>" class="s-blue"><?=$data['title']?></a>
											</h5>
											<p class="card-text s-height-45px s-clamp-text"><?=substr(strip_tags($data['article']), 0, 250)?></p>
											<p class="card-text">
												<img src="<?=$photo?>" width="25" height="25" class="rounded-circle s-pp-border-2x"/>
												<small><a href="<?=SROOT?>user.php?u=<?=$data['uname']?>" data-toggle="tooltip" title="<?=$data['uname']?>" class="s-blue"><?=$data['uname']?></a></small>
												<span class="s-bull" aria-hidden="true">&bull;</span> <small class="text-muted"><?=Datetime::timeTranslate($data['date_added'])?></small>
												<span class="s-bull" aria-hidden="true">&bull;</span> <i class="fas fa-thumbtack" aria-hidden="true" data-toggle="tooltip" title="Pinned post"></i>
											</p>
										</div>
									</div>
								</div>
								
							<?php endwhile; ?>

						<?php else: ?>

							<div class="s-no-data-msg">There are no pinned posts available</div>

						<?php endif; ?>
					</div>
				</section>

				<section class="s-section-area"  id="trends">
					<h2 class="s-section-area-header">Trending stories!</h2>
					<div class="row">
						<?php $selectQueryTrending = $db->query('SELECT __posts.id, __posts.slug, __posts.title, __posts.thumbnail, __posts.article, __posts.date_added, __users.uname, __users.photo FROM __posts JOIN __users ON __posts.user_id = __users.id WHERE __posts.section = ? AND __posts.status = ? ORDER BY __posts.id DESC', ['trending', 'approved']);
						
						if($selectQueryTrending->row_count() > 0):

							while($data = $selectQueryTrending->results()):
								if(!empty($data['thumbnail'])){
									$thumbnail = IMG_CDN_URL.'images/'.$data['thumbnail'];
								}else{
									$thumbnail = IMG_CDN_URL.'default/default-thumbnail-960x540.png';
								}
								if(!empty($data['photo'])){
									$photo = IMG_CDN_URL.'photos/'.$data['photo'];
								}else{
									$photo = IMG_CDN_URL.'default/default-profile-picture-1280x1280.png';
								}?>
								<div class="col-12 col-sm-6 col-md-4 mb-2">
									<div class="card">
										<img class="card-img-top" src="<?=$thumbnail?>" alt="<?=$data['title']?>">
										<div class="card-body">
											<h5 class="card-title s-blue">
												<a href="<?=SROOT?>forum.php?p=<?=$data['slug']?>" data-toggle="tooltip" title="<?=$data['title']?>" class="s-blue"><?=$data['title']?></a>
											</h5>
											<p class="card-text s-height-45px s-clamp-text"><?=substr(strip_tags($data['article']), 0, 250)?></p>
											<p class="card-text">
												<img src="<?=$photo?>" width="25" height="25" class="rounded-circle s-pp-border-2x"/>
												<small><a href="<?=SROOT?>user.php?u=<?=$data['uname']?>" data-toggle="tooltip" title="<?=$data['uname']?>" class="s-blue"><?=$data['uname']?></a></small>
												<span class="s-bull" aria-hidden="true">&bull;</span> <small class="text-muted"><?=Datetime::timeTranslate($data['date_added'])?></small>
												<span class="s-bull" aria-hidden="true">&bull;</span> <i class="fas fa-fire-alt" aria-hidden="true" data-toggle="tooltip" title="Trending post"></i>
											</p>
										</div>
									</div>
								</div>
								
							<?php endwhile; ?>

						<?php else: ?>

							<div class="s-no-data-msg">There are no trending stories available</div>

						<?php endif; ?>
					</div>
				</section>

				<section class="s-section-area">
					<div class="row" id="displayData"></div>
				</section>
			</div>
		</div>

		<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
		<script src="https://unpkg.com/popper.js@1.12.6/dist/umd/popper.js" integrity="sha384-fA23ZRQ3G/J53mElWqVJEGJzU0sTs+SvzG8fXVWP+kJQ1lwFAOkcUOysnlKJC33U" crossorigin="anonymous"></script>
		<script type="text/javascript" src="<?=SROOT?>assets/bootstrap-material-design-4.1.1-dist/js/bootstrap-material-design.min.js"></script>
		<script type="text/javascript" src="<?=SROOT?>assets/js/script.js?v=20200414"></script>
		<script type="text/javascript">
			$("#displayData").loaddata({data_url:'<?=SROOT?>includes/fetch-index.php', end_record_text:'No posts to load'});
		</script>
	</body>
</html>