<?php
use app\core\FrontController;

define('APP_PATH', __DIR__.'/../');

require(__DIR__ . '/../frame/autoload.php');

$webApp = FrontController::getInstance();
$webApp->run('dev');