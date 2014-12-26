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
        $res = null;
        $user = new Users();
        $userInfo = $user->get();
        if($userInfo){
            header('Location: /home');
        }

        if ($this->getRequest()->isPost()) {
            $res = $user->authorize(isset($_POST['rememberMe']));
            if (Users::isAuthorized()) {
                $data = $user->get();
                $this->render('site/home', ['user' => $data]);
            }
        }
        $this->render('users/login', ['error' => $res]);
    }

    public function registrationAction()
    {
        $user = new Users();
        $data = $user->get();
        if($data){
            $this->render('site/home', ['user' => $data]);
        }

        if($this->getRequest()->isPost()) {
            $res = $user->create();
                if ($res === true){
                    if (!$user->sendMail()) {
                        die('Не удалось отправить сообщение!!!');
                    }
                    else {
                        $messages = "На ваш почтовый адрес отправлено сообщение со ссылкой для активации аккаунта.";
                        $this->render('users/info', ['messages' => $messages]);
                        return;
                    }
                }else {
                    $this->render('users/registration', ['error' => $res]);
                    return;
                }
        }
        $this->render('users/registration');
    }

    public function logoutAction()
    {
        $user = new Users();
        $userInfo = $user->get();
        setcookie('sid', '', time() - 3600);
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
        $dataUser = $users->get();

        $category=new Category();
        $ads=new Ads($category,$users);

        $ads_per_page=3;

        if(empty($users->getUid())){
            header("Location: http://{$_SERVER['HTTP_HOST']}/");
            return;
        }

        $page=isset($_GET['page'])?$_GET['page']:1;


        $number=$ads->getNumberOfAds($users->getUid());
        $escape=($page==1)?0:(($page-1)*$ads_per_page);
        $pages=ceil($number/$ads_per_page);


        if($pages==$page){
            $escape=$number-$ads_per_page;
            $ads_per_page=$number;
        }

        $this->render('users/profile',[
            'dbinfo'=>$ads->getAdsByUserId($users->getUid(),$escape,$ads_per_page),
            'ads_per_page'=>$ads_per_page,'pages'=>$pages,'number'=>$number,'selectedPage'=>$page,'user' => $dataUser]);


        //$this->render('users/profile',['dbinfo'=>$ads->getAdsByUserId($dataUser['id']), 'user' => $dataUser]);

    }

    public function editAction()
    {
        $message = null;
        $users = new Users();
        $data = $users->get();
        if($this->getRequest()->isPost()){
            $error = $users->edit();
            if(!empty($error)) {
                $this->render('users/edit-profile', ['error' => $error, 'user' => $data]);
            }
            $message = "Информация успешно обновлена!";
            $users= new Users();
            $data = $users->get();
        }
        $this->render('users/edit-profile', ['user' => $data, 'message' => $message]);
    }

}