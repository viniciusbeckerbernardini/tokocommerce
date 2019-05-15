<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once("vendor/autoload.php");

use \Slim\Slim;

$app = new Slim();

$app->config('debug', true);

require_once($_SERVER['DOCUMENT_ROOT']."/util/functions.php");
require_once($_SERVER['DOCUMENT_ROOT']."/routes/site.php");
require_once($_SERVER['DOCUMENT_ROOT']."/routes/admin.php");
require_once($_SERVER['DOCUMENT_ROOT']."/routes/admin-users.php");
require_once($_SERVER['DOCUMENT_ROOT']."/routes/admin-categories.php");
require_once($_SERVER['DOCUMENT_ROOT']."/routes/admin-products.php");

$app->run();

?>