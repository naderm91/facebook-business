<?php
//dd($_SESSION);

try {
    $campaign_id = createCampaign(
        $_ENV['APP_ID'],
        $_ENV['APP_SECRET'],
        $_SESSION['fb_business']['access_token'],
        $_SESSION['fb_business']['act_account_id'],
        $_SESSION['fb_business']['pixel_id'],
        $_SESSION['fb_business']['product_catalog_id'],
        $_SESSION['fb_business']['product_set_id'],
        $_SESSION['fb_business']['page_id']
    );
} catch (\Facebook\Exceptions\FacebookSDKException $e) {
}

//dd($campaign_id);

header("Location: " . $_ENV['APP_URL']);
