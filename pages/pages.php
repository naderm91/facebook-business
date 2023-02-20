<?php

try {
    $records = getBusinessOwnedPages(
        $_ENV['APP_ID'],
        $_ENV['APP_SECRET'],
        $_SESSION['fb_business']['access_token'],
        $_SESSION['fb_business']['business_id']
    );
} catch (\Facebook\Exceptions\FacebookSDKException $e) {}


if (count($records) === 1) {
    $_SESSION['fb_business']['page_id'] = $records[0]['id'];
}

foreach ($records as $record) {
    $title = "Page #: {$record['id']} - {$record['name']}";

    if (isset($_SESSION['fb_business']['page_id']) && $record['id'] === $_SESSION['fb_business']['page_id']) {
        echo "<li>$title</li>";
    } else {
        echo "<li><a href='" . $_ENV['APP_URL'] . "/?route=select&key=page_id&value={$record['id']}'>$title</a></li>";
    }
}
