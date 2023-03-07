<?php
use Classes\{Database, Datetime};

// Require config file.
require_once __DIR__.'/../config/config.php';

// Require autoload.
require_once __DIR__.'/../app/helpers/autoload.php';

// Require functions and helpers.
require_once __DIR__.'/../app/helpers/helpers.php';

$db = Database::getInstance();

$page_number = sanitize_int($_POST['page']);
$user_id = sanitize_int($_POST['user_id']);
if(!is_numeric($page_number)){
	header('HTTP/1.1 500 Invalid page number!');
    exit;
}
$item_per_page = 1;
$position = (($page_number-1) * $item_per_page);

$selectQuerySubscription = $db->query("SELECT id, reference, plan, subscription_date, subscription_end FROM __subscriptions WHERE user_id = ? ORDER BY id DESC LIMIT $position, $item_per_page", [$user_id]);

while($data = $selectQuerySubscription->results()): ?>
    <tr>
        <td><?=$data['id']?></td>
        <td><?=$data['reference']?></td>
        <td><?=$data['plan']?></td>
        <td><?=$data['subscription_date']?></td>
        <td><?=$data['subscription_end']?></td>
    </tr>
<?php endwhile; ?>