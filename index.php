<?php

require "./bootstrap.php";

$request = array_merge(
    $_GET,
    (array) json_decode(file_get_contents('php://input'), TRUE),
    ['method' => $_SERVER["REQUEST_METHOD"]]
);

if (!isset($request['route'])) {
    $request['route'] = 'home';
}

if (file_exists('pages/' . $request['route'] . '.php')) {
    require 'pages/' . $request['route'] . '.php';
} elseif (file_exists('pages/' . $request['route'] . '.html')) {
    echo file_get_contents('pages/' . $request['route'] . '.html');
} else {
    header("HTTP/1.1 404 Not Found");
    exit();
}
