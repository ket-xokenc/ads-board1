<?php
use app\core\FrontController;
class BaseController
{
    protected $layout = 'layout';
    protected $view;
    protected $app;
    private $request;
    protected $errors = '';
    public function __construct($request)
    {
        $this->app = FrontController::getInstance();
        $this->view = new View();
        $this->request = $request;
    }
    public function render($filename, $data = array())
    {
        $error = $this->errors;
        $view = $this->view;
        $content=array();
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
}