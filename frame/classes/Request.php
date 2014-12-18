<?php

class Request
{

    protected $params;
    protected $controller;
    protected $action;

    public function __construct($options)
    {
        $this->params = $options['params'];
        $this->controller = $options['controller'];
        $this->action = $options['action'];
    }

    public function getParams()
    {
        return $this->params;
    }

    public function getController()
    {
        return $this->controller;
    }

    public function getAction()
    {
        return $this->action;
    }


}