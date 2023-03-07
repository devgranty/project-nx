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
    $username = sanitize_input($_POST['username']);
    if(!is_numeric($page_number)){
        header('HTTP/1.1 500 Invalid page number!');
        exit;
    }
    $item_per_page = 15;
    $position = (($page_number-1) * $item_per_page);

    if(!empty($username)){
        $selectQuery = $db->query("SELECT id, uname, email, phone, rank, date_added, status FROM __users WHERE uname LIKE '%$username%' LIMIT $position, $item_per_page", []);
    }else{
        $selectQuery = $db->query("SELECT id, uname, email, phone, rank, date_added, status FROM __users ORDER BY id DESC LIMIT $position, $item_per_page");
    }

    while($data = $selectQuery->results()): ?>
        <tr>
            <td scope="row"><?=$data['id']?></td>
            <td><?=$data['uname']?></td>
            <td><a href="mailto:<?=$data['email']?>"><?=$data['email']?></a></td>
            <td><a href="tel:<?=$data['phone']?>"><?=$data['phone']?></a></td>
            <td><?=$data['rank']?></td>
            <td><?=Datetime::setDateTime($data['date_added'])?></td>
            <td><?=$data['status']?></td>
            <td><a href="<?=SROOT?>user-control.php?u=<?=$data['id']?>" class="btn btn-success"><i class="fas fa-eye" aria-hidden="true"></i> View</a></td>
            <td><input type="checkbox" name="checked_<?=$data['id']?>" class="form-check-input"/></td>
        </tr>
    <?php endwhile; ?>
<?php endif; ?>
