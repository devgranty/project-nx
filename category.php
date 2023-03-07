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

if(!empty($_GET['type'])){
	$category = sanitize_input($_GET['type']);
	$category_array = ['business', 'sports', 'entertainment', 'politics', 'health', 'general', 'social', 'crime', 'legal', 'lifestyle', 'international', 'art', 'tech', 'education', 'auto', 'national'];
	if(in_array($category, $category_array)){
		$category = $category;
	}else{
		$category = 'business';
	}
}else{
	Router::redirect(SROOT);
}
?>

<!DOCTYPE html>
<html lang="en" prefix="website: http://ogp.me/ns/website#">
	<head>
		<?php include_once __DIR__.'/includes/gtm.php'; ?>
		<title>Category &#8208; <?=SITE_NAME?></title>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="theme-color" content="#000000">
		<link rel="icon" href="<?=SROOT?>favicon.png" sizes="19x19" type="image/png">
		<link rel="apple-touch-icon" href="<?=SROOT?>assets/icons/icon.png" type="image/png">
		<link rel="stylesheet" type="text/css" href="<?=SROOT?>assets/css/style.css?v=20200407">
		<meta name="robots" content="index">
		<meta name="description" content="View <?=$category?> news on <?=SITE_NAME?>">
		<link rel="canonical" href="<?=SITE_URL.SROOT.get_page_name().'.php?type='.$category?>">
		<meta property="og:title" content="Category &#8208; <?=SITE_NAME?>">
		<meta property="og:type" content="website">
		<meta property="og:image" content="<?=SITE_URL.SROOT?>assets/icons/icon.png">
		<meta property="og:image:type" content="image/png">
		<meta property="og:image:width" content="146">
		<meta property="og:image:height" content="146">
		<meta property="og:url" content="<?=SITE_URL.SROOT.get_page_name().'.php?type='.$category?>">
		<meta property="og:description" content="View <?=$category?> news on <?=SITE_NAME?>">
		<meta property="og:locale" content="en_US">
		<meta property="og:site_name" content="<?=SITE_NAME?>">

		<meta name="twitter:card" content="summary">
		<meta name="twitter:site" content="">
		<meta name="twitter:title" content="Category &#8208; <?=SITE_NAME?>">
		<meta name="twitter:description" content="View <?=$category?> news on <?=SITE_NAME?>">
		<meta name="twitter:image" content="<?=SITE_URL.SROOT?>assets/icons/icon.png">
	</head>
	<body>
		<?php include_once __DIR__.'/includes/gtm-ns.php'; ?>
		<div class="min-vh-100">
			<?php include_once __DIR__.'/includes/nav.php'; ?>
			
			<div class="container">
				<section class="s-section-area">
					<h2 class="s-section-area-header s-text-caps"><?=$category?></h2>
					<div class="row" id="displayData"></div>
				</section>
			</div>
		</div>

		<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
		<script src="https://unpkg.com/popper.js@1.12.6/dist/umd/popper.js" integrity="sha384-fA23ZRQ3G/J53mElWqVJEGJzU0sTs+SvzG8fXVWP+kJQ1lwFAOkcUOysnlKJC33U" crossorigin="anonymous"></script>
		<script type="text/javascript" src="<?=SROOT?>assets/bootstrap-material-design-4.1.1-dist/js/bootstrap-material-design.min.js"></script>
		<script type="text/javascript" src="<?=SROOT?>assets/js/script.js?v=20200414"></script>
		<script type="text/javascript">
			$("#displayData").loaddata({data_url:'<?=SROOT?>includes/fetch-category.php', end_record_text:'No posts to load from this category'}, {'category':'<?=$category?>'});
		</script>
	</body>
</html>