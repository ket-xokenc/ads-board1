<?php

class Users
{
    protected $table = 'users';
    protected $user = array();
    private $is_authorized = false;

    public function __construct()
    {
        $this->db = \Registry::get('database');
//       var_dump($this->db);
    }
    public function create($data)
    {
        $user_exists = $this->getSalt($data['login']);
        if($user_exists)
            throw new \Exception("User exists: " . $data['login'], 1);

        $hashes = $this->passwHash($data['password']);
        $data['password'] = $hashes['hash'];
        $data['salt'] = $hashes['salt'];
        $this->user = $data;

        try {
            $this->db->insert($this->table, $this->user);
        }catch(PDOException $e){
            echo "Database error: ".$e->getMessage();
            die();
        }
        return true;
    }

    public function getSalt($login) {
        return $this->db->fetchOne($this->table, 'salt', ['login' => $login]);
    }

    public function authorize($login, $password, $remember = false)
    {
        $salt = $this->getSalt($login);
        if(!$salt)
            return false;
        $hashes = $this->passwHash($password, $salt);
        $this->user = $this->db->fetchRow($this->table, ['*'], ['login' => $login, 'password' => $hashes['hash']]);

        if($this->user['status'] == 'registered')
            die('Пользователь еще не активирован');
        if($this->user) {
            $this->user_id = $this->user['id'];
            $this->saveSession($remember);
            $this->is_authorized = true;
        }
    }

    public function isAutorized(){
        return $this->is_authorized;
    }

    public function passwHash($password, $salt = false)
    {
        $salt || $salt = uniqid();
        $hash = md5(md5($password . md5(sha1($salt))));
        return ['hash' => $hash, 'salt' => $salt];
    }

    public function saveSession($remember = false, $http_only = true, $days = 7)
    {
        $_SESSION["user_id"] = $this->user_id;

        if ($remember) {
            // Save session id in cookies
            $sid = session_id();

            $expire = time() + $days * 24 * 3600;
            $domain = ""; // default domain
            $secure = false;
            $path = "/";

            $cookie = setcookie("sid", $sid, $expire, $path, $domain, $secure, $http_only);
        }
    }

    public function sendMail()
    {

        $hashCode = $this->user['salt'];
        // теперь нам нужно отправить ссылку на указанную почту, для активации пользователя
        $from = 'kvasenko@ukr.net';
        $subject = "Подтверждение регистрации";
        $message = "Вы подали заявку на регистрацию на сайте . " .
            "Подтвердите свою заявку по предложенной ссылке: " .
            "http://dev.local/confirmation/hash/" . $hashCode;

        // отправляем письмо
        if (!mail($this->user['email'], $subject, $message, 'From: ' . $from))
            // если письмо не отправлено то значит пользователь некорректно указал свою почту
            return false;

        return true;

    }

    public function getById($id)
    {
        return $this->db->fetchRow('users', ['*'], ['id' => $id]);
    }

}