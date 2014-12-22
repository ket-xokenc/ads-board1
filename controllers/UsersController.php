<?php
use application\core\BaseController;
use application\classes\Session;
/**
 * Created by PhpStorm.
 * User: alexandr
 * Date: 09.12.14
 * Time: 14:51
 */
class UsersController extends BaseController
{
    public function __construct($request)
    {
        parent::__construct($request);
    }

    public function loginAction()
    {
        $user = new Users();
        $userInfo = $user->get();
        if($userInfo){
            header('Location: /home');
        }

        if ($this->getRequest()->isPost()) {
            $login = preg_match('/^[a-zA-Z0-9_-]{3,16}$/', $_POST['login']) ? $_POST['login'] : false;
            $password = preg_match('/^[a-zA-Z0-9_-]{3,18}$/', $_POST['password']) ? $_POST['password'] : false;

            if (!$login) {
                $this->errors .= 'Логин может состоять только из букв английского алфавита и цифр';
            }
            if(!$password) {
                $this->errors .= 'Пароль не соответствует правилам составления';
            }
            if(!$login || !$password){
                $this->render('users/login');
                die;
            }

            $user->authorize($login, $password);
            if (Users::isAuthorized()) {
                $data = $user->getByLogin($login);
                $this->render('site/home', ['user' => $data]);
            }
        }
        $this->errors .= $user->getError();
        $this->render('users/login');
    }

    public function registrationAction()
    {
        $user = new Users();
        $data = $user->get();
        if($data){
            $this->render('site/home', ['user' => $data]);
        }

        if($this->getRequest()->isPost()) {
            if (!empty($_POST['name']) && !empty($_POST['login']) && !empty($_POST['password1']) && !empty($_POST['email'])) {
                if ($_POST['password1'] == $_POST['password2']) {

                    $login = preg_match('/^[a-zA-Z0-9_-]{3,16}$/', $_POST['login']) ? $_POST['login'] : false;
                    $password = preg_match('/^[a-zA-Z0-9_-]{3,18}$/', $_POST['password1']) ? $_POST['password1'] : false;
                    $name = preg_match('/^[a-zA-ZА-Яа-я]{3,18}$/', $_POST['name']) ? $_POST['name'] : false;
                    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
                    $phone = preg_match('/^\+7\d{10}$/', $_POST['phone']) ? $_POST['phone'] : false;
                    if(!$login)
                        $this->errors .= "Не правильный логин!<br />";
                    if(!$password)
                        $this->errors .= "Пароль не соответствует правилам заполнения!<br />";
                    if(!$email)
                        $this->errors .= "Не правильный email!<br />";

                    if ($user->create(['login' => $login, 'name' => $name, 'password' => $password, 'email' => $email, 'phone' => $phone])) {
                        if (!$user->sendMail()) {
                            die('Не удалось отправить сообщение!!!');
                        }
                        else {
                            $messages = "На ваш почтовый адрес отправлено сообщение со ссылкой для активации аккаунта.";
                            $this->render('users/info', ['messages' => $messages]);
                            return;
                        }
                    }else {
                        $this->render('users/registration');
                        return;
                    }
                }else{
                    $this->errors .= "Пароли не совпадают<br />";
                    $this->errors .= $user->getError();
                    $this->render('users/registration');
                    return;
                }

            } else {
                $this->errors .= "Не все обязательные поля заполнены!<br />";
                $this->render('users/registration');
                return;

            }
        }
        $this->errors .= $user->getError();
        $this->render('users/registration');
    }

    public function logoutAction()
    {
        Session::destroy();
        $this->render('site/home');

    }

    public function restorePasswordAction()
    {
        $user = new Users();
        if($this->getRequest()->isPost()){
            $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
            if(!$email) {
                $this->errors .= "Не правильно введен email!";
                $this->render('users/restore-password');
                return;
            }
            $userData = $user->getByEmail($email);
            if(!$userData){
                $this->errors .= "Данный email не зарегистрирован на сайте!";
            }else{
                $dataForRestore = $user->savePasswordForRestore($email);
                $dataForRestore['email'] = $email;
                $user->sendMailForRestore($dataForRestore);
                $message = "На ваш email отправлен новый пароль для входа. После авторизации не забудьте изменить его!";
                $this->render('users/info', ['messages' => $message]);
            }

        }

        $this->render('users/restore-password');
    }

    public function confirmationAction()
    {
        $user = new Users();
        $params = $this->getRequest()->getParams();
        $hash = array_shift($params);
        $user->confirm($hash);
        header('Location: /login');
    }

    public function paymentPlanAction()
    {
        $user = new Users();
        $dataInfo = $user->get();
        $this->render('users/payment-plan', ['user' => $dataInfo]);
    }

    public function profileAction()
    {
        $users = new Users();
        $data = $users->get();
        $this->render('users/profile', ['user' => $data]);
    }

    public function editAction()
    {
        $users = new Users();
        $data = $users->get();
        $this->render('users/edit-profile', ['user' => $data]);
    }

}