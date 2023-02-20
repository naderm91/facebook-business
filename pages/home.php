<?php

error_reporting(0);

echo "<body style='padding:0;margin:0;'>";
echo "<style>a {text-decoration: none;padding: 10px 0;}</style>";
echo "<section style='padding: 20px; margin-top: 20px; '>";

if ($expiration_time = validateAccessToken(
    $_ENV['APP_ID'],
    $_ENV['APP_SECRET'],
    isset($_SESSION['fb_business']['access_token']) ? $_SESSION['fb_business']['access_token'] : null
)) {
    echo "<h2>Your access token is valid will expires in <small style='color:red;'>" . date('Y-m-d h:i:s', $expiration_time) . "</small></h2>";

    echo "<br>";
    echo "<br>";
    echo "<strong>Businesses <span style='color:red;'>(required)</span></strong><br>";
    require_once APP_DIR . "/pages/businesses.php";

    if (isset($_SESSION['fb_business']['business_id'])) {
        echo "<br>";
        echo "<br>";
        echo "<strong>Pages owned by selected business <span style='color:red;'>(required)</span></strong><br>";
        require_once APP_DIR . "/pages/pages.php";

        echo "<br>";
        echo "<br>";
        echo "<strong>Catalogs - product sets owned by selected business <span style='color:red;'>(required)</span></strong><br>";
        require_once APP_DIR . "/pages/catalogs.php";
    }

    echo "<br>";
    echo "<br>";

    echo "<strong>Available ad accounts <span style='color:red;'>(required)</span></strong>";
    echo "<br>";
    require_once APP_DIR . "/pages/accounts.php";

    if (isset($_SESSION['fb_business']['page_id'])) {
        echo "<br>";
        echo "<br>";
        echo "<strong>Instagram accounts :</strong>";
        require_once APP_DIR . "/pages/instagram.php";
    }

    if (isset($_SESSION['fb_business']['act_account_id'])) {
        echo "<br>";
        echo "<br>";
        echo "<strong>Available pixels for selected ad account <span style='color:red;'>(required)</span></strong><br>";
        require_once APP_DIR . "/pages/pixels.php";

        echo "<br>";
        echo "<br>";
        echo "<strong>Campaigns for selected ad account:</strong>";
        require_once APP_DIR . "/pages/campaigns.php";
    }

    if (
        isset($_SESSION['fb_business']['business_id'])
        && isset($_SESSION['fb_business']['page_id'])
        && isset($_SESSION['fb_business']['product_set_id'])
        && isset($_SESSION['fb_business']['act_account_id'])
        && isset($_SESSION['fb_business']['pixel_id'])
    ) {
        echo "<br><br><a href='" . ($_ENV['APP_URL'] . '/?route=create_campaign') . "'>Create dummy campaign</a>";
    }

    echo "<br><br><a href='" . ($_ENV['APP_URL'] . '/?route=session_clear') . "'>Clear session</a><br><br>";
} else {
    if (isset($_SESSION['fb_business']['access_token'])) {
        unset($_SESSION['fb_business']['access_token']);
    }
    echo 'Click <a href="' . $_ENV['APP_URL'] . '/?route=auth' . '">here</a> to get your access token!';
}

echo "</section>";

if (isset($_SESSION['fb_business']['access_token'])) {
    echo "<section style=' background: #eee; padding: 25px; margin-top: 20px; '><code><b style='padding-bottom:10px;display: block;'>Current session</b>" . json_encode($_SESSION['fb_business'], JSON_PRETTY_PRINT) . "</code></section>";
}
echo "</body>";
