<?php
namespace application\core;
use application\core\FrontController;
use application\core\View;
class BaseController
{
    protected $layout = 'layout';
    protected $view;
    protected $app;
    private $request;
    private $data = array();

    public function __construct($request = null)
    {
        $this->app = FrontController::getInstance();
        $this->view = new View();
        $this->request = $request;
    }

    public function render($filename, $data = array())
    {
        $error = '';
        $view = $this->view;
        $content=array();
        extract($this->data);

        foreach($data as $k => $v){
            $$k = $v;
        }
        if(is_array($filename)){
            foreach($filename as $file){
                ob_start();
                include_once '../views/'.$file.'.phtml';
                $content[explode('/',$file)[1]] = ob_get_clean();
            }
        }else{
            ob_start();
            include_once '../views/'.$filename.'.phtml';
            $content = ob_get_clean();
        }

        include_once '../views/layouts/'.$this->layout.'.phtml';
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function assign($key, $value)
    {
        $this->data[$key]= $value;
    }
}