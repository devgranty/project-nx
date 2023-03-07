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

use Classes\{Database, Datetime, Hash, Session};

$db = Database::getInstance();
$session = Session::startSession();

if(!empty($_POST['page']) && !empty($_POST['event_type'])):
    $page_number = sanitize_int($_POST['page']);
    $event_type = sanitize_input($_POST['event_type']);
    if(!is_numeric($page_number)){
        header('HTTP/1.1 500 Invalid page number!');
        exit;
    }
    $item_per_page = 15;
    $position = (($page_number-1) * $item_per_page);

    if($event_type == 'post'){
        $selectQuery = $db->query("SELECT title AS event_subject, date_added, status FROM __posts WHERE user_id = ? ORDER BY id DESC LIMIT $position, $item_per_page", [Session::get('uid')]);
    }elseif($event_type == 'comment'){
        $selectQuery = $db->query("SELECT comment AS event_subject, date_added, status FROM __comments WHERE user_id = ? ORDER BY id DESC LIMIT $position, $item_per_page", [Session::get('uid')]);
    }

    while($data = $selectQuery->results()): ?>
        <div class="col-12 col-sm-4">
            <div class="card border-dark mb-3">
                <div class="card-header"><?=$event_type?> <span aria-hidden="true">&#124;</span> <?=Datetime::setDateTime($data['date_added'])?></div>
                <div class="card-body text-dark">
                    <p class="card-title"><?=$data['event_subject']?></p>
                </div>
                <div class="card-footer bg-transparent">
                    <small class="text-muted"><?=$data['status']?></small>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
<?php endif; ?>