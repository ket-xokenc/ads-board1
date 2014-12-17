<?php
namespace app\core;
class FrontController
{
    /**
     * @var null
     */
    static $instance = null;

    /**
     *
     */
    private function __construct()
    {
        $this->initSession();
    }

    public function __clone()
    {
        trigger_error('Cloning the FrontController is not permitted', E_USER_ERROR);
    }

    /**
     * @return FrontController|null
     */
    static function getInstance()
    {
        if(self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @param null $path
     * @throws \Exception
     */
    public function run($path = null)
    {
        $this->initConfigs($path);
        $this->initRoutes();
        $controllerName = $this->controller;
        $controllerObj = new $controllerName(new \Request(
                        array('params' => $this->params,
                            'controller' => $this->controller,
                              'action' => $this->action)
            )
        );
        $methodName = $this->action;
        if (method_exists($controllerObj, $methodName)) {
            $controllerObj->$methodName();
        } else {
            throw new \Exception('action not found');
        }
    }

    protected function initSession()
    {
        session_start();
    }

    /**
     * @param $path
     */
    protected function initConfigs($path)
    {
        $settings = \Config::init($path);

        foreach($settings as $k => $v){
            \Registry::set($k, $v);
        }
    }

    /**
     *
     */
    protected function initRoutes()
    {
        $route = new \Route($this);
        $routes = $route->getUri();

        $this->controller = $routes['controller'];
        $this->action = $routes['action'];
        $this->params = $routes['params'];
    }

}