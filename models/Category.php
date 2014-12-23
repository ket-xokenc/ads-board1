<?php

use application\classes\Registry as Registry;

class Category {

    private $db;

    const TABLE='categories';


    public function __construct()
    {
        $this->db = Registry::get('database');
    }

    public function create(){
        $table=Ads::TABLE;
        $data=array();
        $errorLog=array();

        if(empty(trim($_POST['name']))){
            $errorLog['name']='Enter category name';
        }else{
            if(strlen($_POST['name'])<5){
               $errorLog['name']='Too short category name';
            }elseif(strlen($_POST['name'])>15){
               $errorLog['name']='Too long category name';
            }else{
               $data['name']= $_POST['name'];
            }
        }

        if(empty(trim($_POST['description']))){
            $errorLog['description']='Enter category description';
        }else{
            if(strlen($_POST['description'])<15){
                $errorLog['name']='Too short category description';
            }elseif(strlen($_POST['description'])>15){
                $errorLog['description']='Too long category description';
            }else{
                $data['description']= $_POST['description'];
            }
        }

        if(empty($errorLog)){
            $this->db->insert($table,$data);
        }else{
            return $errorLog;
        }
    }

    public function edit($category_id){
        $table=Ads::TABLE;
        $data=array();
        $errorLog=array();

        if(empty(trim($_POST['name']))){
            $errorLog['name']='Enter category name';
        }else{
            if(strlen($_POST['name'])<5){
                $errorLog['name']='Too short category name';
            }elseif(strlen($_POST['name'])>15){
                $errorLog['name']='Too long category name';
            }else{
                $data['name']= $_POST['name'];
            }
        }

        if(empty(trim($_POST['description']))){
            $errorLog['description']='Enter category description';
        }else{
            if(strlen($_POST['description'])<15){
                $errorLog['description']='Too short category description';
            }elseif(strlen($_POST['description'])>15){
                $errorLog['description']='Too long category description';
            }else{
                $data['description']= $_POST['description'];
            }
        }

        if(empty($this->getCategoryById($category_id))){
            $errorLog['category_id']="Category don't exist";
        }

        if(empty($errorLog)){
            $this->db->update($table,$data.['category_id'=>$category_id]);
        }else{
            return $errorLog;
        }
    }


    public function delete($category_id){
        $table=Ads::TABLE;

        $this->db->delete($table,['category_id'=>$category_id]);
    }

    public function getCategoryById($id){
        $table=Ads::TABLE;

        return $this->db->fetchRow($table, ['*'], ['category_id' => $id]);
    }

    public function getCategoryByName($name){
        $table=Ads::TABLE;

        return $this->db->fetchRow($table, ['*'], ['name' => $name]);
    }

} 