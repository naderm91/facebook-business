<?php

$redirect_uri =  $_ENV['APP_URL'] . '/?route=access_token';
$home_uri =  $_ENV['APP_URL'] . '/?route=home';

try {
    /** @var array $request */
    if ($request['code']){
        $access_token = getAccessToken($_ENV['APP_ID'], $_ENV['APP_SECRET'], $redirect_uri, $request['code']);
        $long_lived_token = getLongLivedAccessToken($_ENV['APP_ID'], $_ENV['APP_SECRET'], $access_token);
        $_SESSION['fb_business']['access_token'] = $long_lived_token;
    }
    // Redirect the user to the home
    header('Location: ' . $home_uri);
}
catch (\Throwable $th) {
    echo json_encode(['status' => 'ERR', 'error_message' => $th->getMessage()]);
}
