<?php
use application\classes\Registry;
use application\classes\Session;

class Admin
{
    private $db;

    public function __construct()
    {
        $this->db = Registry::get('database');
    }

    function showUsers()
    {
        return $this->db->isAdmin('users');
    }

    function ban($id)
    {
        $this->db->update('users', ['status' => 'banned'], ['id' => $id]);
    }

    function unban($id)
    {
        $this->db->update('users', ['status' => 'registered'], ['id' => $id]);
    }
    function showAds($id)
    {
       return $this->db->fetchAll('ad', ['title', 'text'], ['id_user'=>$id]);
    }
}