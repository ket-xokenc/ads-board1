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
        if($this->isPost()){
            if(!empty($_POST['login']) && !empty($_POST['password1'])) {
                $login = preg_match('/^[a-z0-9_-]{3,16}$/', $_POST['login']) ? $_POST['login'] : false;
                $password = preg_match('/^[a-z0-9_-]{3,18}$/', $_POST['password1']) ? $_POST['password1'] : false;;

                $user->authorize($login, $password);
                if($user->isAutorized())
                {
                    header('Location: /home');
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
                $login = $_POST['login'];
                $pass = $_POST['password1'];
                $name = $_POST['name'];
                $email = $_POST['email'];
                if($user->create(['login' => $login, 'name' => $name, 'password' => $pass, 'email' => $email]))
                {
                    echo 'Пользователь успешно добавлен';exit;
                }
            }
        }

        $this->render('users/registration');
    }

}