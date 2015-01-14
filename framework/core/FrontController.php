<?php
namespace application\core;
use application\classes\Registry;
use application\classes\Request;
use application\classes\Database;
use application\classes\Route;
use application\classes\Config;
use application\classes\Session;
use application\classes\Acl;

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
        $acl=new Acl();
        $this->initConfigs($path);
        $this->initRoutes();

        $controllerName = $this->controller;
        $methodName = $this->action;

        if(!$acl->access($controllerName,$methodName)) {
            header("Location:http://{$_SERVER['HTTP_HOST']}/");
            return;
        }

        $controllerObj = new $controllerName(new Request(
                        array('params' => $this->params,
                            'controller' => $this->controller,
                              'action' => $this->action)
            )
        );

        if (method_exists($controllerObj, $methodName)) {
            $controllerObj->$methodName();
        } else {
            throw new \Exception('action not found');
        }
    }

    protected function initSession()
    {
        Session::init();
    }

    /**
     * @param $path
     */
    protected function initConfigs($path)
    {
        $settings = Config::init($path);

        foreach($settings as $k => $v){
            Registry::set($k, $v);
        }
        Registry::set('database', new Database());
    }

    /**
     *
     */
    protected function initRoutes()
    {
        $route = new Route($this);
        $routes = $route->getUri();

        $this->controller = $routes['controller'];
        $this->action = $routes['action'];
        $this->params = $routes['params'];
    }

}