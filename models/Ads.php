<?php

use application\classes\Registry as Registry;


class Ads {

    private $db;

    const TABLE='ad';


    public function __construct()
    {
        $this->db = Registry::get('database');
    }

    public function create(){
        $table=Ads::TABLE;
        $data=array();
        $errorLog=array();
        $category=new Category();
        $user=new Users();

        $data['id_user']=$user->get()['id'];

        $data['date_create']=date('Y-m-d');


        if(empty(trim($_POST['title']))){
            $errorLog['title']='Empty title field';
        }else{
            if(strlen($_POST['title'])<10){
                $errorLog['title']='Too short title';
            }elseif(strlen($_POST['title'])>30){
                $errorLog['title']='Too long title';
            }else{
                $data['title']= $_POST['title'];
            }
        }

        if(empty(trim($_POST['text']))){
            $errorLog['text']='Empty description field';
        }else{
            if(strlen($_POST['text'])<20){
                $errorLog['text']='Too short description';
            }elseif(strlen($_POST['text'])>100){
                $errorLog['text']='Too long description';
            }else{
                $data['text']= $_POST['text'];
            }
        }

        if(empty($category->getCategoryByName($_POST['category']))){
            $errorLog['category']="Category {$_POST['category']} don't exist";
        }else{
            $data['category_id']=$category->getCategoryByName($_POST['category'])['category_id'];
        }

        if(empty($errorLog)){
            $this->db->insert($table,$data);
        }else{
            return $errorLog;
        }
    }

    public function edit($ads_id){
        $table=Ads::TABLE;
        $data=array();
        $errorLog=array();
        $category=new Category();
        $user=new Users();

        $data['id_user']=$user->get()['id'];

        $data['date_create']=date('Y-m-d');

        if(empty($this->getAdsById($ads_id))){
            $errorLog['id_ad']="Ads don't exist";
        }


        if(!empty(trim($_POST['title']))){

            if(strlen($_POST['title'])<10){
                $errorLog['title']='Too short title';
            }elseif(strlen($_POST['title'])>30){
                $errorLog['title']='Too long title';
            }else{
                $data['title']= $_POST['title'];
            }
        }

        if(!empty(trim($_POST['text']))){

            if(strlen($_POST['text'])<20){
                $errorLog['text']='Too short description';
            }elseif(strlen($_POST['text'])>100){
                $errorLog['text']='Too long description';
            }else{
                $data['text']= $_POST['text'];
            }
        }


        if(empty($errorLog)){
            $this->db->update($table,$data,['id_ad'=>$ads_id]);
        }else{
            return $errorLog;
        }

    }


    public function delete($ads_id){
        $table=Ads::TABLE;

        $this->db->delete($table,['id_ad'=>$ads_id]);
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


} 