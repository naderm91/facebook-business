<?php

error_reporting(0);

$accounts = getActAccounts($_ENV['APP_ID'], $_ENV['APP_SECRET'], $_SESSION['fb_business']['access_token']);

foreach ($accounts as $account) {
    $title = "Ad Account #: {$account['id']} - {$account['name']}";
    if (isset($_SESSION['fb_business']['act_account_id']) && $account['id'] === $_SESSION['fb_business']['act_account_id']) {
        echo "<li>$title</li>";
    } else {
        echo "<li><a href='" . $_ENV['APP_URL'] . "/?route=select&key=act_account_id&value={$account['id']}'>$title</a></li>";
    }
}
