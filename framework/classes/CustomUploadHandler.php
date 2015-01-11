<?php


namespace application\classes;
use application\classes\UploadHandler;

class CustomUploadHandler extends UploadHandler{

    private $params;

    public function __construct($options,$uploadPath,$params=null){

        $this->params=$params;

        $opt=['upload_dir' => dirname($this->get_server_var('SCRIPT_FILENAME')).$uploadPath,
            'upload_url' => $this->get_full_url().$uploadPath];
        $upldOptions=$options+$opt;
        parent::__construct($upldOptions);
    }

    protected function get_user_id() {
        if(isset($this->params)){
            return $this->params[0];
        }
        return Session::get('user_id');
    }

} 