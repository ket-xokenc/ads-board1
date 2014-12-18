<?php

class Users
{
    public function __construct()
    {
        $this->db = \Registry::get('database');
//       var_dump($this->db);
    }
    public function getById($id)
    {
        return $this->db->fetchRow('users', ['*'], ['id' => $id]);
    }

    public function getByLoginAndPassw($login, $pasw)
    {
        return $this->db->fetchRow('users', ['*'], ['login' => $login, 'password' => $pasw]);
    }
}