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
        $selectQuery = $db->query("SELECT __accounts.id, __accounts.user_id, __accounts.amount, __accounts.date_added, __accounts.status, __users.uname FROM __accounts JOIN __users ON __accounts.user_id = __users.id WHERE __accounts.type = ? AND __users.uname LIKE '%$username%' ORDER BY __accounts.id DESC LIMIT $position, $item_per_page", ['debit']);
    }else{
        $selectQuery = $db->query("SELECT __accounts.id, __accounts.user_id, __accounts.amount, __accounts.date_added, __accounts.status, __users.uname FROM __accounts JOIN __users ON __accounts.user_id = __users.id WHERE __accounts.type = ? ORDER BY FIELD(__accounts.status, 'pending', 'unpaid', 'paid'), __users.referral_count DESC LIMIT $position, $item_per_page", ['debit']);
    }

    while($data = $selectQuery->results()): ?>
        <tr>
            <td scope="row"><?=$data['id']?></td>
            <td><a href="<?=SROOT?>user-control.php?u=<?=$data['user_id']?>"><?=$data['uname']?></a></td>
            <td><?=$data['amount']?></td>
            <td><?=Datetime::setDateTime($data['date_added'])?></td>
            <td><?=$data['status']?></td>
            <td><input type="checkbox" name="checked_<?=$data['id']?>_<?=$data['user_id']?>" class="form-check-input"/></td>
        </tr>
    <?php endwhile; ?>
<?php endif; ?>