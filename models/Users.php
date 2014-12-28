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
    }
    public function create()
    {
        $login = preg_match('/^[a-zA-Z0-9_-]{3,16}$/', $_POST['login']) ? $_POST['login'] : false;
        $password = preg_match('/^[a-zA-Z0-9_-]{3,18}$/', $_POST['password1']) ? $_POST['password1'] : false;
        $name = preg_match('/^[a-zA-ZА-Яа-я]{3,18}$/', $_POST['name']) ? $_POST['name'] : false;
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        $phone = preg_match('/^\+38\d{10}$/', $_POST['phone']) ? $_POST['phone'] : false;

        if(!$name) {
            return $this->errorValid = "Не правильно введено имя!<br />";
        }
        if(!$login) {
            return $this->errorValid = "Не правильно введен логин!<br />";
        }

        if(!$email){
            return $this->errorValid = "Введите правильный email!<br />";
        }
        if(!$password){
            return $this->errorValid = "Пароль не соответствует правилам заполнения<br />";
        }
        if($password !== $_POST['password2']) {
            return $this->errorValid = "Пароли не совпадают<br />";
        }

        $user_exists = $this->getSalt($login);
        if($user_exists) {
            return $this->errorValid .= "Пользователь с таким логином уже существует<br />";
        }

        $hashes = $this->passwHash($password);
        $data['login'] = $login;
        $data['name'] = $name;
        $data['email'] = $email;
        $data['password'] = $hashes['hash'];
        $data['salt'] = $hashes['salt'];
        $data['phone'] = $phone;
        $data['date_create'] = date('Y-m-d H:i:s');

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

    public function authorize($remember = false)
    {
        $this->clearUsers();
        $login = preg_match('/^[a-zA-Z0-9_-]{3,16}$/', $_POST['login']) ? $_POST['login'] : false;
        $password = preg_match('/^[a-zA-Z0-9_-]{3,18}$/', $_POST['password']) ? $_POST['password'] : false;
        if (!$login) {
            return $this->errorValid = 'Логин должен состоять только из букв английского алфавита и цифр';
        }
        if(!$password) {
            return $this->errorValid = 'Пароль не соответствует правилам составления';
        }

        $salt = $this->getSalt($login);
        if(!$salt) {
            return $this->errorValid = "Пользователь с таким логином не существует!<br />";
        }
        $hashes = $this->passwHash($password, $salt);
        $this->user = $this->db->fetchRow($this->table, ['*'], ['login' => $login, 'password' => $hashes['hash']]);
        if(!$this->user) {
            return $this->errorValid = "Не правильно введен пароль!<br />";
        }
        if($this->user['status'] == 'registered') {
            return $this->errorValid = "Пользователь еще не активирован.<br />";
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
        //$this->db->query("DELETE FROM users WHERE date_create > :minn AND status = 'registered'", $where);
    }

    public function get($userId = null)
    {
        //$this->clearUsers();

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
            return $this->user_id;

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

    public function edit()
    {
        $name = preg_match('/^[a-zA-ZА-Яа-я]{3,18}$/', trim($_POST['name'])) ? $_POST['name'] : false;
        $login = preg_match('/^[a-zA-Z0-9_-]{3,16}$/', trim($_POST['login'])) ? $_POST['login'] : false;
        $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
        $phone = preg_match('/^\+38\d{10}$/', trim($_POST['phone'])) ? $_POST['phone'] : false;

        if(!empty($_POST['old_password'])) {
            $passwordOld = preg_match('/^[a-zA-Z0-9_-]{3,18}$/', trim($_POST['old_password'])) ? $_POST['old_password'] : false;
            $passwordNew = preg_match('/^[a-zA-Z0-9_-]{3,18}$/', trim($_POST['password1'])) ? $_POST['password1'] : false;
            $passwordConfirm = preg_match('/^[a-zA-Z0-9_-]{3,18}$/', trim($_POST['password2'])) ? $_POST['password2'] : false;
            if(!$passwordOld)
                $this->errorValid .= "Старий пароль введен не правильно<br />";
            if (!$passwordNew || !$passwordConfirm)
                $this->errorValid .= "Ошибка при вводе нового пароля<br />";
            if($passwordNew !== $passwordConfirm)
                $this->errorValid .= "Пароли не совпадают<br />";
            if(!empty($this->errorValid)) return $this->errorValid;
        }

        if(!$name) {
            $this->errorValid .= "Не правильно введено имя!<br />";
        }
        if(!$login) {
            $this->errorValid .= "Не правильно введен логин!<br />";
        }
        if(!$email){
            $this->errorValid .= "Введите правильный email!<br />";
        }
        if(isset($passwordOld) && isset($passwordNew)) {
            $hashes = $this->passwHash($passwordOld, $this->user['salt']);
           // print_r($this->passwHash('123', $this->user['salt']));exit;
            if($this->user['password'] !== $hashes['hash']){
                $this->errorValid .= "Введен не правильный старый пароль!<br />";
            }
            else {
                $hashes = $this->passwHash($passwordNew);;

            }
        }

        if(!empty($this->errorValid)){
            return $this->errorValid;
        }

        $this->db->update($this->table, ['login' => $login, 'name' => $name, 'email' => $email,
            'password' => $passw = !empty($hashes['hash']) ? $hashes['hash'] : $this->user['password'] ,
            'salt' => $salt = !empty($hashes['salt']) ? $hashes['salt'] : $this->user['salt'],
            'phone' => $phone
        ], ['id' => $this->user_id]);




    }

}