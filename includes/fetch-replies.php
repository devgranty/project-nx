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

if(!empty($_POST['page'])):
    $page_number = sanitize_int($_POST['page']);
    if(!is_numeric($page_number)){
        header('HTTP/1.1 500 Invalid page number!');
        exit;
    }
    $item_per_page = 15;
    $position = (($page_number-1) * $item_per_page);

    $user_replies = $db->query("SELECT __replies.id, __replies.user_id, __replies.broadcast_id, __replies.reply, __replies.date_added, __users.uname FROM __replies JOIN __users ON __replies.user_id = __users.id WHERE __replies.seen = ? ORDER BY __replies.id DESC LIMIT $position, $item_per_page", [0]);

    while($data = $user_replies->results()): ?>
        <div class="card bg-light mb-3">
            <div class="card-header"><?=$data['uname']?> <span aria-hidden="true">&#124;</span> <?=Datetime::setDateTime($data['date_added'])?> <span aria-hidden="true">&#124;</span> <strong class="s-black">#<?=$data['broadcast_id']?></strong></div>
            <div class="card-body">
                <p class="card-text"><?=$data['reply']?></p>
            </div>
            <div class="card-footer bg-transparent">
                <form class="form-inline mb-0" role="form" action="<?=SROOT?>replies.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="r" value="<?=$data['id']?>"/>
                    <input type="hidden" name="form_token" value="<?=Hash::hashUseHmac('sha256', 'replies', Session::get('_token'))?>"/>
                    <button type="submit" name="mark_reply_read" class="btn btn-success"><i class="fas fa-check" aria-hidden="true"></i> Mark as read</button>
                    <a href="<?=SROOT?>user-control.php?u=<?=$data['user_id']?>#userReply" class="btn btn-default"><i class="fas fa-eye" aria-hidden="true"></i> View all replies from user</a>
                </form>
            </div>
        </div>
    <?php endwhile; ?>
<?php endif; ?>