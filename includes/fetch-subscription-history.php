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

use Classes\{Database, Datetime, Session};

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

    $selectQuerySubscription = $db->query("SELECT id, reference, plan, subscription_date, subscription_end FROM __subscriptions WHERE user_id = ? ORDER BY id DESC LIMIT $position, $item_per_page", [Session::get('uid')]);

    while($data = $selectQuerySubscription->results()): ?>
        <tr>
            <td scope="row"><?=$data['id']?></td>
            <td><?=$data['reference']?></td>
            <td><?=$data['plan']?></td>
            <td><?=Datetime::setDateTime($data['subscription_date'])?></td>
            <td><?=Datetime::setDateTime($data['subscription_end'])?></td>
        </tr>
    <?php endwhile; ?>
<?php endif; ?>