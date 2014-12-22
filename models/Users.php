<?php
use application\classes\Registry;
use application\classes\Session;
class Users
{
    protected $table = 'users';
    protected $user = array();
    private $is_authorized = false;
    private $user_id = null;
    private $sid;
    private $errorValid = '';

    public function __construct()
    {
        $this->db = Registry::get('database');
//         var_dump($this->db);
        //
    }
    public function create($data)
    {
        $user_exists = $this->getSalt($data['login']);

        if($user_exists) {
            $this->errorValid .= "Пользователь с таким логином уже существует";
            return false;
        }
        if(!$data['password'])
            return false;
        $hashes = $this->passwHash($data['password']);
        $data['password'] = $hashes['hash'];
        $data['salt'] = $hashes['salt'];
        $data['create_time'] = date('Y-m-d H:i:s');;

        $this->user = $data;

        try {
            $this->db->insert($this->table, $this->user);
        }catch(PDOException $e){
            echo "Database error: ".$e->getMessage();
            die();
        }
        return true;
    }

    public function getError(){
        return $this->errorValid;
    }

    public function getSalt($login) {
        return $this->db->fetchOne($this->table, 'salt', ['login' => $login]);
    }

    public function authorize($login, $password, $remember = false)
    {
        $salt = $this->getSalt($login);
        if(!$salt) {
            $this->errorValid .= "Пользователь с таким логином не существует!<br />";
            return false;
        }
        $hashes = $this->passwHash($password, $salt);
        $this->user = $this->db->fetchRow($this->table, ['*'], ['login' => $login, 'password' => $hashes['hash']]);
        if(!$this->user) {
            $this->errorValid .= "Не правильно введен пароль!<br />";
            return false;
        }
        if($this->user['status'] == 'registered') {
            $this->errorValid .= "Пользователь еще не активирован.<br />";
            return false;
        }
        if($this->user) {
            $this->user_id = $this->user['id'];
            $this->saveSession($remember);
            $this->is_authorized = true;
        }
        else return;

    }


    public static function isAuthorized()
    {
        if (!empty($_SESSION["user_id"])) {
            return (bool) $_SESSION["user_id"];
        }
        return false;
    }


    public function passwHash($password, $salt = false)
    {
        $salt || $salt = uniqid();
        $hash = md5(md5($password . md5(sha1($salt))));
        return ['hash' => $hash, 'salt' => $salt];
    }

    public function saveSession($remember = false, $http_only = true, $days = 7)
    {
        $_SESSION["user_id"] = $this->user['id'];
        $guid = $this->generateStr();
        $this->db->update($this->table, ['guid' => $guid], ['id' => $this->user_id]);

        if ($remember) {
            $expire = time() + $days * 24 * 3600;
            $domain = ""; // default domain
            $secure = false;
            $path = "/";

            $cookie = setcookie("sid", $guid, $expire, $path, $domain, $secure, $http_only);
        }
    }

    public function clearUsers()
    {
        $min = date('Y-m-d H:i:s', time() - 60 * 10);
        $where = [':minn' => $min];
        $this->db->query("DELETE FROM users WHERE create_time < :minn AND status = 'registered'", $where);
    }

    public function get($userId = null)
    {
       // $this->clearUsers();

        if($userId === null)
            $this->user_id = $this->getUid();

        if($this->user_id == null)
            return false;

        $this->user = $this->db->fetchRow($this->table, ['*'], ['id' => $this->user_id]);
        return $this->user;
    }

    private function generateStr($length = 10)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRQSTUVWXYZ0123456789";
        $code = "";
        $clen = strlen($chars) - 1;

        while (strlen($code) < $length)
            $code .= $chars[mt_rand(0, $clen)];

        return $code;
    }



    public function getUid()
    {
        // Проверка кеша.
        if ($this->user_id != null)
            return $this->uid;

        // Берем по текущей сессии.


        $uId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null ;
        if($uId)
            return $uId;

        if(isset($_COOKIE['sid']) && !$uId){

            $guid = $_COOKIE['sid'];
            $uid = $this->db->fetchOne($this->table, 'id', ['guid' => $guid]);
            return $uid;
        }
        return false;
    }

    public function sendMail()
    {

        $hashCode = $this->user['salt'];
        // теперь нам нужно отправить ссылку на указанную почту, для активации пользователя
        $from = 'kvasenko@ukr.net';
        $subject = "Подтверждение регистрации";
        $message = "Вы подали заявку на регистрацию на сайте . " .
            "Подтвердите свою заявку по предложенной ссылке: " .
            "http://".$_SERVER['SERVER_NAME']."/confirmation/hash/" . $hashCode;

        // отправляем письмо
        if (!mail($this->user['email'], $subject, $message, 'From: ' . $from))
            // если письмо не отправлено то значит пользователь некорректно указал свою почту
            return false;

        return true;

    }

    public function savePasswordForRestore($email)
    {
        $data = $this->getByEmail($email);
        $pass = $this->generateStr(6);
        $hashes = $this->passwHash($pass);

        $this->db->update($this->table, ['password' => $hashes['hash'], 'salt' => $hashes['salt']], ['email' => $email]);
        return ['login' => $data['login'], 'password' => $pass];
    }

    public function sendMailForRestore($data)
    {
        extract($data);
        $from = 'framework@ukr.net';
        $subject = "Восстановление пароля";

        $message = "Вам сгенерировано новый пароль.
				Логин: ".$login.'; Пароль: '.$password;

        // отправляем письмо
        if (!mail($email, $subject, $message, 'From: ' . $from))
            // если письмо не отправлено то значит пользователь некорректно указал свою почту
            return false;

        return true;
    }

    public function confirm($hash){
        $this->db->update($this->table, ['status' => 'confirmed'], ['salt' => $hash]);
    }

    public function getById($id)
    {
        return $this->db->fetchRow('users', ['*'], ['id' => $id]);
    }

    public function getByLogin($login)
    {
        return $this->db->fetchRow('users', ['*'], ['login' => $login]);
    }

    public function getByEmail($email)
    {
        return $this->db->fetchRow('users', ['*'], ['email' => $email]);
    }

}