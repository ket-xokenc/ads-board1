<?php
class Route
{
    private $routes;
    private $controller;
    private $action;
    private $params = array();

    public function __construct($app){
        // Получаем конфигурацию из файла.
        $settings = Registry::get();
        $this->routes = $settings['routes'];
        $this->controller = $settings['defaultController'];
        $this->action = $settings['defaultAction'];
    }

    public function parseRequest(){
        if(!empty($_SERVER['REQUEST_URI'])) {
            return trim($_SERVER['REQUEST_URI'], '/');
        }

        if(!empty($_SERVER['PATH_INFO'])) {
            return trim($_SERVER['PATH_INFO'], '/');
        }

        if(!empty($_SERVER['QUERY_STRING'])) {
            return trim($_SERVER['QUERY_STRING'], '/');
        }
    }

    public function getUri(){
        // Получаем URI.
        $uri = $this->parseRequest();
        if (($pos = strpos($uri, '?'))) {

            $uri = substr($uri, 0, $pos);
        }
        // Пытаемся применить к нему правила из конфигуации.
        foreach($this->routes as $pattern => $route){
            // Если правило совпало.
            if(preg_match("~^$pattern$~", $uri)){
                // Получаем внутренний путь из внешнего согласно правилу.
                $internalRoute = preg_replace("~^$pattern$~", $route, $uri);
                // Разбиваем внутренний путь на сегменты.
                $segments = explode('/', $internalRoute);
//                print_r($segments);
                // Первый сегмент — контроллер.
                $this->controller = ucfirst(array_shift($segments)).'Controller';
                // Второй — действие.
                $this->action = array_shift($segments).'Action';
                // Остальные сегменты — параметры.
                //print_r(count($segments));
                $this->params = $segments;

                // Подключаем файл контроллера, если он имеется
                //echo $this->controller;
                //$controllerFile = APP_PATH.'/controllers/'.$this->controller.'.php';
                //echo $controllerFile;
                //if(file_exists($controllerFile)){
                  //  include($controllerFile);
              //  }
                if(!is_callable(array($this->controller, $this->action))){
                    header("HTTP/1.0 404 Not Found");
                    return;
                }
                //$controller = new $this->controller();
                // Вызываем действие контроллера с параметрами
                return array('controller' => $this->controller,
                            'action' => $this->action,
                            'params' => $this->params);
            }
        }
        if(empty($uri)) {
            $controllerFile = APP_PATH.'/controllers/'.$this->controller.'.php';
            if(file_exists($controllerFile)){
                include($controllerFile);
            }
            if(!is_callable(array($this->controller, $this->action))){
                header("HTTP/1.0 404 Not Found");
                return;
            }
            return array('controller' => $this->controller,
                'action' => $this->action,
                'params' => $this->params);
        }

        // Ничего не применилось. 404.
        header("HTTP/1.0 404 Not Found");
        return;
    }

}