<?php

$records = getBusinesses(
    $_ENV['APP_ID'],
    $_ENV['APP_SECRET'],
    $_SESSION['fb_business']['access_token']
);

if (count($records) === 1) {
    $_SESSION['fb_business']['business_id'] = $records[0]['id'];
}

foreach ($records as $record) {
    $title = "Business #: {$record['id']} - {$record['name']}";

    if (isset($_SESSION['fb_business']['business_id']) && $record['id'] === $_SESSION['fb_business']['business_id']) {
        echo "<li>$title</li>";
    } else {
        echo "<li><a href='" . $_ENV['APP_URL'] . "/?route=select&key=business_id&value={$record['id']}'>$title</a></li>";
    }
}
