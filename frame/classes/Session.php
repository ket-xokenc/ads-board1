<?php

class Session {

    private static $_sessionStarted = false;

    public static function init(){

        if(self::$_sessionStarted == false){
            session_start();
            self::$_sessionStarted = true;
        }

    }

    public static function set($key,$value = false){

        if(is_array($key) && $value === false){

            foreach ($key as $name => $value) {
                $_SESSION[$name] = $value;
            }

        } else {
            $_SESSION[$key] = $value;
        }

    }

    public static function get($key,$secondkey = false){

        if($secondkey == true){

            if(isset($_SESSION[$key][$secondkey])){
                return $_SESSION[$key][$secondkey];
            }

        } else {
            if(isset($_SESSION[$key])){
                return $_SESSION[$key];
            }

        }

        return false;
    }

    public static function id() {
        return session_id();
    }

    public static function display(){
        return $_SESSION;
    }

    public static function destroy($key='') {
        if(self::$_sessionStarted == true) {

            if(empty($key)) {
                session_unset();
                session_destroy();
            } else {
                unset($_SESSION[$key]);
            }

        }
    }

}