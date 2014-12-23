<?php

use application\classes\Registry as Registry;


class Ads {

    private $db;

    const TABLE='ad';


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


    public function getAdsById($id){
        $table=Ads::TABLE;

        return $this->db->fetchRow($table, ['*'], ['id_ad' => $id]);
    }

    public function getAdsByUserId($id){
        $table=Ads::TABLE;

        return $this->db->fetchAll($table, ['*'], ['id_user' => $id]);
    }

    public function getAdsByCategoryId($id){
        $table=Ads::TABLE;

        return $this->db->fetchAll($table, ['*'], ['category_id' => $id]);
    }

    public function getAdsByCategoryName($name){
        $table=Ads::TABLE;

        return $this->db->query("Select * from $table inner join categories on
                                    $table.category_id=categories.category_id where categories.name=:name",array(':name'=>$name));
    }

    public function getAdsByUserName($name){
        $table=Ads::TABLE;

        return $this->db->query("Select * from users inner join $table on
                                    $table.id_user=users.id where users.name=:name",array(':name'=>$name));
    }

    public function getAdsByTitle($title,$is_regex=false){
        $table=Ads::TABLE;

        if(!$is_regex){
            $title='^'.$title.'$';
        }

        return $this->db->query("SELECT * FROM $table WHERE title REGEXP :title",array(':title'=>$title));
    }

    public function getAdsByText($text,$is_regex=false){
        $table=Ads::TABLE;

        if(!$is_regex){
            $text='^'.$text.'$';
        }

        return $this->db->query("SELECT * FROM $table WHERE text REGEXP :text",array(':text'=>$text));
    }

    public function getAdsByDate($date,$is_regex=false){
        $table=Ads::TABLE;

        if(!$is_regex){
            $date='^'.$date.'$';
        }

        return $this->db->query("SELECT * FROM $table WHERE text REGEXP :date",array(':date'=>$date));
    }

    private function checkData($data){

        $errorLog=array();

        if(empty($data['date_create'])){
            $data['date_create']=date('Y-m-d');
        }

        if(strlen($data['title'])<10){
            $errorLog['title']='Too short title';
        }

        if(strlen($data['title'])>30){
            $errorLog['title']='Too long title';
        }

        if(strlen($data['text'])<20){
            $errorLog['text']='Too short description';
        }

        if(strlen($data['text'])>100){
            $errorLog['text']='Too long description';
        }

        if(empty($this->db->fetchRow('categories',['*'],$data['category_id']))){
            $errorLog['category_id']="Category with id {$data['category_id']} don't exist";
        }

        if(empty($this->db->fetchRow('users',['*'],$data['id_user']))){
            $errorLog['id_user']="User with id {$data['id_user']} don't exist";
        }

        return $errorLog;
    }
} 