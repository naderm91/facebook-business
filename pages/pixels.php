<?php

// pixels
error_reporting(0);

$records = getPixelIds($_ENV['APP_ID'], $_ENV['APP_SECRET'], $_SESSION['fb_business']['access_token'], $_SESSION['fb_business']['act_account_id']);

foreach ($records as $record) {
    $status = $record['can_proxy'] ? 'active' : 'inactive';
    $title = "Pixel #: {$record['id']} - {$record['name']} ($status)";
    
    if (isset($_SESSION['fb_business']['pixel_id']) && $record['id'] === $_SESSION['fb_business']['pixel_id']) {
        echo "<li>$title</li>";
    } else {
        echo "<li><a href='" . $_ENV['APP_URL'] . "/?route=select&key=pixel_id&value={$record['id']}'>$title</a></li>";
    }
}
