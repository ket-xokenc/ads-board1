<?php
use app\core\FrontController;

class BaseController
{
    protected $layout = 'layout';
    protected $view;
    protected $app;

    public function __construct()
    {
        $this->app = FrontController::getInstance();
        $this->view = new View();
    }

    public function render($filename, $data = array())
    {

        $view = $this->view;
        $content=array();

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

    public function isPost($key = false)
    {
        if($key) {
            if($_POST[$key])
                return true;
        }
        if($_SERVER['REQUEST_METHOD'] == 'POST')
            return true;
        return false;
    }
}