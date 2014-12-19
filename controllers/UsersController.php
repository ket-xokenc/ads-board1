<?php
/**
 * Created by PhpStorm.
 * User: alexandr
 * Date: 09.12.14
 * Time: 14:51
 */
class UsersController extends BaseController
{
    public function loginAction()
    {
        $user = new Users();
        if ($this->isPost()) {
            if (!empty($_POST['login']) && !empty($_POST['password'])) {
                $login = preg_match('/^[a-z0-9_-]{3,16}$/', $_POST['login']) ? $_POST['login'] : false;
                $password = preg_match('/^[a-z0-9_-]{3,18}$/', $_POST['password']) ? $_POST['password'] : false;;
                if (!$login or !$password)
                    $errorInfo = 'Не правильно введена логин или пароль';
                $user->authorize($login, $password);
                if ($user->isAutorized()) {
                    //header('Location: /home');
                    $data = $user->getByLogin($login);
                    $this->render('site/home', ['user' => $data]);
                }
            }
        }

//        $user = $model->create(['login' => 'qqq', 'password' => 111, 'name' => 'qqqq', 'email' => 'qqq@ukr.net']);
//       print_r($user);exit;
        $this->render('users/login');
    }

    public function registrationAction()
    {
        $user = new Users();
        if($this->isPost()){
            if(!empty($_POST['name'])&& !empty($_POST['login']) && !empty($_POST['password1']) &&
                $_POST['password1'] === $_POST['password2']){
                $login = preg_match('/^[a-z0-9_-]{3,16}$/', $_POST['login']) ? $_POST['login'] : false;
                $password = preg_match('/^[a-z0-9_-]{3,18}$/', $_POST['password1']) ? $_POST['password1'] : false;
                $name = preg_match('/^[a-zA-ZА-Яа-я0-9_-]{3,18}$/', $_POST['name']) ? $_POST['name'] : false;
                $email = preg_match('/^([a-z0-9_\.-]+)@([a-z0-9_\.-]+)\.([a-z\.]{2,6})$/', $_POST['email']) ? $_POST['email'] : false;
                if($user->create(['login' => $login, 'name' => $name, 'password' => $password, 'email' => $email]))
                {
                    if(!$user->sendMail())
                        die('Не удалось отправить сообщение!!!');
                    header('Location: /info');
                }
            }
        }
        if ($id = Session::get('user_id')) {
            $data = $user->getById($id);
        }

        $this->render('users/registration');
    }

    public function logoutAction()
    {
        Session::destroy('user_id');
        $this->render('site/home');

    }
    public function infoAction()
    {
        $this->render('user/info', ['messages' => 'На ваш эмэил выслана ссылка для подтверждения регистрации']);
    }

}