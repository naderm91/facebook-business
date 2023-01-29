<?php

if (isset($request['key']) && isset($request['value'])) {
    $keys = explode(',', $request['key']);
    $values = explode(',', $request['value']);

    foreach ($keys as $index => $key) {
        if (isset($values[$index])) {
            $_SESSION['fb_business'][trim($key)] = trim($values[$index]);
        }
    }
}

header("Location: " . $_ENV['APP_URL']);
