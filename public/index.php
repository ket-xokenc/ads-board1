<?php
use app\core\FrontController;
error_reporting('E_ALL');
ini_set('display_errors', 1);

define('APP_PATH', __DIR__.'/../');

require(__DIR__ . '/../frame/autoload.php');

$webApp = FrontController::getInstance();
$webApp->run('dev');