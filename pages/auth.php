<?php

$redirect_uri = $_ENV['APP_URL'] . '/?route=access_token';

$url = requestAuth($_ENV['APP_ID'], $redirect_uri);

// Redirect the user to the login dialog
header('Location: ' . $url);
