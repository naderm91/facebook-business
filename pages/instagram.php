<?php

if (!isset($_SESSION['fb_business']['instagram_actor_id'])){
    $instagramRecords = getInstagramPackedAccountID(
        $_ENV['APP_ID'],
        $_ENV['APP_SECRET'],
        $_SESSION['fb_business']['access_token']
    );

    if (count($instagramRecords) === 1) {
        $_SESSION['fb_business']['instagram_actor_id']= $instagramRecords[0]['id'];
    }
}

$title = "ID #:".$_SESSION['fb_business']['instagram_actor_id'];
echo "<br>";
echo "<li>$title</li>";
