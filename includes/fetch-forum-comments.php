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

use Classes\{Database, Datetime, Router, Session};

$db = Database::getInstance();
$session = Session::startSession();

if(!empty($_POST['page']) && !empty($_POST['post_id'])):
    $page_number = sanitize_int($_POST['page']);
    $post_id = sanitize_int($_POST['post_id']);
    if(!is_numeric($page_number)){
        header('HTTP/1.1 500 Invalid page number!');
        exit;
    }
    $item_per_page = 8;
    $position = (($page_number-1) * $item_per_page);

    $selectQueryComment = $db->query("SELECT __comments.id, __comments.user_id, __comments.comment, __comments.date_added, __users.uname, __users.photo FROM __comments JOIN __users ON __comments.user_id = __users.id WHERE __comments.post_id = ? AND __comments.status = ? ORDER BY __comments.id DESC LIMIT $position, $item_per_page", [$post_id, 'approved']);

    while($data = $selectQueryComment->results()):
        if(!empty($data['photo'])){
            $photo = IMG_CDN_URL.'photos/'.$data['photo'];
        }else{
            $photo = IMG_CDN_URL.'default/default-profile-picture-1280x1280.png';
        }?>
        <div class="card mb-1">
            <div class="card-body">
                <div class="d-inline-flex">
                    <img src="<?=$photo?>" width="45" height="45" class="rounded-circle s-pp-border-2x">
                    <p class="card-text p-1 ml-1">
                        <a href="<?=SROOT?>user.php?u=<?=$data['uname']?>" data-toggle="tooltip" title="<?=$data['uname']?>"><?=$data['uname']?></a>
                        <span class="text-dark"><?=$data['comment']?></span>
                    </p>
                </div>
            </div>
            <div class="card-footer bg-transparent">
                <div class="card-text text-muted">
                    <small><i class="fas fa-clock"></i> <?=Datetime::timeTranslate($data['date_added'])?></small> 
                    <?php if(Session::exists('uid')): ?>
                        <?php if(Session::get('uid') === $data['user_id']): ?>
                            <span class="s-bull" aria-hidden="true">&bull;</span>
                            <div class="s-dropdown">
                                <button class="text-primary small" style="border:none; background:none;"><i class="fas fa-ellipsis-v"></i> More</button>
                                <div class="s-dropdown-content">
                                    <ul class="list-unstyled">
                                        <li><a role="button" href="#" data-toggle="modal" data-target="#confirmCommentDeleteModal" class="text-danger" onclick="document.getElementById('comment_id').value = <?=$data['id']?>; document.getElementById('user_id').value = <?=$data['user_id']?>;"><i class="fas fa-trash-alt"></i> Delete</a></li>
                                    </ul>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
<?php endif; ?>
