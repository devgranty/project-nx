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

use Classes\{Database, Datetime};

$db = Database::getInstance();

if(!empty($_POST['page'])):
    $page_number = sanitize_int($_POST['page']);
    if(!is_numeric($page_number)){
        header('HTTP/1.1 500 Invalid page number!');
        exit;
    }
    $item_per_page = 15;
    $position = (($page_number-1) * $item_per_page);

    $selectQuery = $db->query("SELECT __posts.id, __posts.user_id, __posts.title, __posts.category, __posts.section, __posts.date_added, __posts.status, __users.uname FROM __posts JOIN __users ON __posts.user_id = __users.id ORDER BY __posts.id DESC LIMIT $position, $item_per_page", []);

    while($data = $selectQuery->results()): ?>
        <tr>
            <td scope="row"><?=$data['id']?></td>
            <td><a href="<?=SROOT?>user-control.php?u=<?=$data['user_id']?>"><?=$data['uname']?></a></td>
            <td><?=$data['title']?></td>
            <td><?=$data['category']?></td>
            <td><?=$data['section']?></td>
            <td><?=Datetime::setDateTime($data['date_added'])?></td>
            <td><?=$data['status']?></td>
            <td><a href="<?=SROOT?>post-control.php?p=<?=$data['id']?>" class="btn btn-success"><i class="fas fa-eye" aria-hidden="true"></i> View</a></td>
        </tr>
    <?php endwhile; ?>
<?php endif; ?>