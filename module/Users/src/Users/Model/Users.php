<?php
namespace Users\Model;

class Users
{
    public $id;
    public $name;
    public $login;
    public $email;
    public $password;
    public $role;

    public function exchangeArray($data)
    {
        foreach ($data as $k => $v) {
            if (property_exists($this, $k)) {
                $this->$k = (isset($v)) ? $v : null;
            }
        }
    }

    public function setPassword($clearPassword)
    {
        $this->password = md5($clearPassword);
    }
}