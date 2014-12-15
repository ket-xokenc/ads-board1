<?php
require(__DIR__ .'/core/FrontController.php');

function __autoload($className)
{
    if (file_exists(APP_PATH.'/frame/classes/'.$className.'.php')) {
        require_once(APP_PATH.'/frame/classes/'.$className.'.php');
    } else if (file_exists(APP_PATH.'/controllers/'.$className.'.php')) {
        require_once(APP_PATH.'/controllers/'.$className.'.php');
    } else if (file_exists(APP_PATH.'/models/'.$className.'.php')) {
        require_once(APP_PATH.'/models/'.$className.'.php');
    }else if (file_exists(APP_PATH.'/frame/core/'.$className.'.php')) {
        require_once(APP_PATH.'/frame/core/'.$className.'.php');
    }
}
