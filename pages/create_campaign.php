<?php

use Facebook\Exceptions\FacebookSDKException;

try {
    createCampaign(
        $_ENV['APP_ID'],
        $_ENV['APP_SECRET'],
        $_SESSION['fb_business']['access_token'],
        $_SESSION['fb_business']['act_account_id'],
        $_SESSION['fb_business']['pixel_id'],
        $_SESSION['fb_business']['product_catalog_id'],
        $_SESSION['fb_business']['product_set_id'],
        $_SESSION['fb_business']['page_id'],
        $_SESSION['fb_business']['instagram_actor_id'],
    );
} catch (FacebookSDKException $e) {}

header("Location: " . $_ENV['APP_URL']);
