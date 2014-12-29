<?php
namespace application\core;

use application\classes\Validator;
use application\classes\Registry;

class Model{

    protected $validator;
    protected $db;

    protected function __construct(){
        $this->validator=new Validator();
        $this->db=Registry::get('database');
    }

}