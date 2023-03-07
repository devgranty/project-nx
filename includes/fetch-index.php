<?php
/**
 * ---------------------------------------
 * LOAD UP APPLICATION
 * ---------------------------------------
 * Let us load up all helpers, classes
 * and application configuration.
 * Feels good to take a nap now!
 */
require_once __DIR__.'/../bootstrap/app.php';

use Classes\{Database, Datetime, Router};

$db = Database::getInstance();

if(!empty($_POST['page'])):
	$page_number = sanitize_int($_POST['page']);
	if(!is_numeric($page_number)){
		header('HTTP/1.1 500 Invalid page number!');
		exit;
	}
	$item_per_page = 6;
	$position = (($page_number-1) * $item_per_page);

	$selectQuerynormal = $db->query("SELECT __posts.id, __posts.slug, __posts.title, __posts.thumbnail, __posts.article, __posts.date_added, __users.uname, __users.photo FROM __posts JOIN __users ON __posts.user_id = __users.id WHERE __posts.section = ? AND __posts.status = ? ORDER BY __posts.id DESC LIMIT $position, $item_per_page", ['normal', 'approved']);

	if($selectQuerynormal->row_count() > 0):

		while($data = $selectQuerynormal->results()):
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
						</p>
					</div>
				</div>
			</div>
		<?php endwhile; ?>
	<?php endif; ?>
<?php endif; ?>
