<?php

use application\classes\Registry as Registry;

class Category {

    private $db;

    const TABLE='categories';


    public function __construct()
    {
        $this->db = Registry::get('database');
    }

    public function create($data){
        $table=Ads::TABLE;

        if(!empty($errors=$this->checkData($data))){
            return $errors;
        }

        $this->db->insert($table,$data);
    }

    public function edit($data,$where){
        $table=Ads::TABLE;

        if(!empty($errors=$this->checkData($data))){
            return $errors;
        }

        $this->db->update($table,$data,$where);
    }


    public function delete($where){
        $table=Ads::TABLE;

        $this->db->delete($table,$where);
    }

    public function getCategoryById($id){
        $table=Ads::TABLE;

        return $this->db->fetchRow($table, ['*'], ['category_id' => $id]);
    }

    public function getCategoryByName($name){
        $table=Ads::TABLE;

        return $this->db->fetchRow($table, ['*'], ['name' => $name]);
    }


    private function checkData($data){

        $errorLog=array();

        if(strlen($data['name'])<5){
            $errorLog['title']='Too short name';
        }

        if(strlen($data['name'])>15){
            $errorLog['title']='Too long name';
        }

        return $errorLog;
    }

} 