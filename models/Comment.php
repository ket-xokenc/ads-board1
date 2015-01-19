<?php


use application\classes\Registry;
use application\classes\Session;
use application\core\Model;

class Comment extends Model
{
    public function __construct()
    {
        parent::__construct();
        $this->validator->setRules($this->validRules());
        $this->validator->setMessages($this->validMessages());
    }

    private function validRules()
    {
        return [
            'body' => [
                'type' => 'text',
                'maxlength' => '1024',
                'minlength' => '5'
                ],
        ];
    }

    private function validMessages()
    {
        return [
            'body' => [
                'maxlength' => 'Too long text',
                'minlength' => 'Too short text',
            ],
        ];
    }

    public  function addComment()
    {
        $errorLog = array();
        $data = [];
        $data['body'] = $_POST['body'];
        if (($errorLog = $this->validator->validate($data)) !== true) {
            $errorLog['status'] = 'error';
            return $errorLog;
        }
        $userId = Session::get('user_id');
        $adsId = (int) $_POST['ad_id'];
        $dateCreate = date('Y-m-d H:i:s');
        $data['date_create'] = $dateCreate;
        $data['status'] = 'ok';


        $this->db->insert('comments', ['user_id' => $userId, 'ad_id' => $adsId, 'text' => $_POST['body'], 'date_create' => $dateCreate]);
        return $data;
    }

    public function getCommentsByAdId($adId)
    {
        $data = $this->db->query("
            SELECT comments.text,  comments.`date_create` create_comment, users.name user_name FROM comments
                INNER JOIN users ON users.id = comments.`user_id` WHERE comments.`ad_id` = :adId
        ", [':adId' => $adId]);
        return $data;
    }
}