<?php


class Ads {

    private $db;
    const TABLE='ad';

    public function __construct()
    {
        $this->db = \Registry::get('database');
    }

    public function create($data){
        $table=Ads::TABLE;

        if(empty($data['date_create'])){
            $data['date_create']=date('Y-m-d');
        }

        $this->db->insert($table,$data);
    }

    public function edit($data,$where){
        $table=Ads::TABLE;

        if(empty($data['date_create'])){
            $data['date_create']=date('Y-m-d');
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
            $title='^'.$text.'$';
        }

        return $this->db->query("SELECT * FROM $table WHERE text REGEXP :text",array(':text'=>$text));
    }

    public function getAdsByDate($date,$is_regex=false){
        $table=Ads::TABLE;

        if(!$is_regex){
            $title='^'.$date.'$';
        }

        return $this->db->query("SELECT * FROM $table WHERE text REGEXP :date",array(':date'=>$date));
    }

} 