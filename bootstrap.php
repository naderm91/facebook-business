<?php
session_start();
define('APP_DIR', __DIR__);

require './vendor/autoload.php';
require_once APP_DIR . "/lib/fb_business.php";

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if (!isset($_SESSION['fb_business'])) {
    $_SESSION['fb_business'] = [];
    $_SESSION['fb_business']['start_time'] = time();
}
