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
if(!Session::exists('uid')) Router::redirect('signin.php');
// Check if user is an editor
if(Session::get('rank') == 'editor') Router::redirect('403.php');

(int)$post_count = $db->selectQuery('__posts', ['id'])->row_count();

(int)$comment_count = $db->selectQuery('__comments', ['id'])->row_count();

(int)$user_editor_count = $db->selectQuery('__users', ['id'], ['WHERE' => ['rank' => 'editor']])->row_count();

(int)$user_moderator_count = $db->selectQuery('__users', ['id'], ['WHERE' => ['rank' => 'moderator']])->row_count();

(int)$user_admin_count = $db->selectQuery('__users', ['id'], ['WHERE' => ['rank' => 'admin']])->row_count();

(int)$subscription_tester_count = $db->selectQuery('__subscriptions', ['id'], ['WHERE' => ['plan' => 'tester']])->row_count();

(int)$subscription_basic_count = $db->selectQuery('__subscriptions', ['id'], ['WHERE' => ['plan' => 'basic']])->row_count();

(int)$subscription_bronze_count = $db->selectQuery('__subscriptions', ['id'], ['WHERE' => ['plan' => 'bronze']])->row_count();

(int)$subscription_silver_count = $db->selectQuery('__subscriptions', ['id'], ['WHERE' => ['plan' => 'silver']])->row_count();

(int)$subscription_emerald_count = $db->selectQuery('__subscriptions', ['id'], ['WHERE' => ['plan' => 'emerald']])->row_count();

(int)$subscription_jasper_count = $db->selectQuery('__subscriptions', ['id'], ['WHERE' => ['plan' => 'jasper']])->row_count();

(int)$subscription_ruby_count = $db->selectQuery('__subscriptions', ['id'], ['WHERE' => ['plan' => 'ruby']])->row_count();

(int)$subscription_gold_count = $db->selectQuery('__subscriptions', ['id'], ['WHERE' => ['plan' => 'gold']])->row_count();

(int)$total_subscriptions_amount = 
$subscription_tester_count*(planAmount('tester')/100) + 
$subscription_basic_count*(planAmount('basic')/100) + 
$subscription_bronze_count*(planAmount('bronze')/100) + 
$subscription_silver_count*(planAmount('silver')/100) + 
$subscription_emerald_count*(planAmount('emerald')/100) + 
$subscription_jasper_count*(planAmount('jasper')/100) + 
$subscription_ruby_count*(planAmount('ruby')/100) + 
$subscription_gold_count*(planAmount('gold')/100);
if(empty($total_subscriptions_amount)){
	$total_subscriptions_amount = 0;
}

(int)$total_cashout_amount = $db->query('SELECT SUM(amount) AS total_cashout_amount FROM __accounts WHERE type = ? AND status = ?', ['debit', 'paid'])->results()['total_cashout_amount'];
if(empty($total_cashout_amount)){
	$total_cashout_amount = 0;
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr" prefix="website: http://ogp.me/ns/website#">
	<head>
		<?php include_once __DIR__.'/includes/gtm.php'; ?>
		<title>Site stats &#8208; <?=SITE_NAME?></title>
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
                <h2 class="text-center s-blue">Site stats</h2>

                <div class="row">
                    <div class="col-12 col-sm-4">
                        <div class="card border-dark mb-3">
                            <div class="card-body text-dark">
                                <h5 class="card-title">Posts</h5>
                                <p class="card-text"><?=$post_count?></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-4">
                        <div class="card border-dark mb-3">
                            <div class="card-body text-dark">
                                <h5 class="card-title">Comments</h5>
                                <p class="card-text"><?=$comment_count?></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-4">
                        <div class="card border-dark mb-3">
                            <div class="card-body text-dark">
                                <h5 class="card-title">Editors</h5>
                                <p class="card-text"><?=$user_editor_count?></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-4">
                        <div class="card border-dark mb-3">
                            <div class="card-body text-dark">
                                <h5 class="card-title">Moderators</h5>
                                <p class="card-text"><?=$user_moderator_count?></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-4">
                        <div class="card border-dark mb-3">
                            <div class="card-body text-dark">
                                <h5 class="card-title">Admins</h5>
                                <p class="card-text"><?=$user_admin_count?></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-4">
                        <div class="card border-dark mb-3">
                            <div class="card-body text-dark">
                                <h5 class="card-title">Testers</h5>
                                <p class="card-text"><?=$subscription_tester_count?></p>
                            </div>
                        </div>
                    </div>


                    <div class="col-12 col-sm-4">
                        <div class="card border-dark mb-3">
                            <div class="card-body text-dark">
                                <h5 class="card-title">Basics</h5>
                                <p class="card-text"><?=$subscription_basic_count?></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-4">
                        <div class="card border-dark mb-3">
                            <div class="card-body text-dark">
                                <h5 class="card-title">Bronzes</h5>
                                <p class="card-text"><?=$subscription_bronze_count?></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-4">
                        <div class="card border-dark mb-3">
                            <div class="card-body text-dark">
                                <h5 class="card-title">Silvers</h5>
                                <p class="card-text"><?=$subscription_silver_count?></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-4">
                        <div class="card border-dark mb-3">
                            <div class="card-body text-dark">
                                <h5 class="card-title">Emeralds</h5>
                                <p class="card-text"><?=$subscription_emerald_count?></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-4">
                        <div class="card border-dark mb-3">
                            <div class="card-body text-dark">
                                <h5 class="card-title">Jaspers</h5>
                                <p class="card-text"><?=$subscription_jasper_count?></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-4">
                        <div class="card border-dark mb-3">
                            <div class="card-body text-dark">
                                <h5 class="card-title">Rubies</h5>
                                <p class="card-text"><?=$subscription_ruby_count?></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-4">
                        <div class="card border-dark mb-3">
                            <div class="card-body text-dark">
                                <h5 class="card-title">Golds</h5>
                                <p class="card-text"><?=$subscription_gold_count?></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-4">
                        <div class="card border-dark mb-3">
                            <div class="card-body text-dark">
                                <h5 class="card-title">Total subscription (amount)</h5>
                                <p class="card-text"><span aria-hidden="true">&#8358;</span><?=$total_subscriptions_amount?>.00</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-4">
                        <div class="card border-dark mb-3">
                            <div class="card-body text-dark">
                                <h5 class="card-title">Total cashout (amount)</h5>
                                <p class="card-text"><span aria-hidden="true">&#8358;</span><?=$total_cashout_amount?>.00</p>
                            </div>
                        </div>
                    </div>
                </div>

			</div>
		</div>

        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script src="https://unpkg.com/popper.js@1.12.6/dist/umd/popper.js" integrity="sha384-fA23ZRQ3G/J53mElWqVJEGJzU0sTs+SvzG8fXVWP+kJQ1lwFAOkcUOysnlKJC33U" crossorigin="anonymous"></script>
		<script type="text/javascript" src="<?=SROOT?>assets/bootstrap-material-design-4.1.1-dist/js/bootstrap-material-design.min.js"></script>
		<script type="text/javascript" src="<?=SROOT?>assets/js/script.js?v=20200414"></script>
	</body>
</html>