<?php
use application\core\BaseController;
use application\classes\Session;
use application\classes\Registry;
use application\classes\Paypal;
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
        $plans = new Plans();
        $dataPlans = $plans->getActivePlans();
        $this->render('users/payment-plan', ['user' => $dataInfo, 'plans' => $dataPlans]);
    }

    public function profileAction()
    {
        $users = new Users();
        $dataUser = $users->get();

        $category=new Category();
        $ads=new Ads($category);

        $this->render('users/profile',['dbinfo'=>$ads->getAdsByUserId($dataUser['id']), 'user' => $dataUser]);

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

    public function subscribePaymentPlanAction()
    {
        $users = new Users();
        $params = $this->getRequest()->getParams();
        $siteUrl = Registry::get('siteUrl');

        // Параметры нашего запроса
        $requestParams = array(
            'RETURNURL' => 'http://'.$siteUrl.'/payment/success',
            'CANCELURL' => 'http://'.$siteUrl.'/payment/cancelled'
        );

        $orderParams = array(
            'PAYMENTREQUEST_0_AMT' => '500',
            'PAYMENTREQUEST_0_SHIPPINGAMT' => '4',
            'PAYMENTREQUEST_0_CURRENCYCODE' => 'RUB',
            'PAYMENTREQUEST_0_ITEMAMT' => '496'
        );

        $item = array(
            'L_PAYMENTREQUEST_0_NAME0' => 'iPhone',
            'L_PAYMENTREQUEST_0_DESC0' => 'White iPhone, 16GB',
            'L_PAYMENTREQUEST_0_AMT0' => '496',
            'L_PAYMENTREQUEST_0_QTY0' => '1'
        );

        $paypal = new Paypal();
        $response = $paypal -> request('SetExpressCheckout',$requestParams + $orderParams + $item);

        if(is_array($response) && $response['ACK'] == 'Success') { // Запрос был успешно принят
            $token = $response['TOKEN'];
            header( 'Location: https://www.sandbox.paypal.com/webscr?cmd=_express-checkout&useraction=commit&token= ' . urlencode($token) );
        }
    }

    public function paymentSuccessAction()
    {
        if( isset($_GET['token']) && !empty($_GET['token']) ) { // Токен присутствует
            // Получаем детали оплаты, включая информацию о покупателе.
            // Эти данные могут пригодиться в будущем для создания, к примеру, базы постоянных покупателей
            $paypal = new Paypal();
            $checkoutDetails = $paypal -> request('GetExpressCheckoutDetails', array('TOKEN' => $_GET['token']));

            // Завершаем транзакцию
            $requestParams = array(
                'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',
                'PAYERID' => $_GET['PayerID']
            );

            $response = $paypal -> request('DoExpressCheckoutPayment',$requestParams);
            if( is_array($response) && $response['ACK'] == 'Success') { // Оплата успешно проведена
                // Здесь мы сохраняем ID транзакции, может пригодиться во внутреннем учете
                $transactionId = $response['PAYMENTINFO_0_TRANSACTIONID'];
                echo 'Ура!!!';
            }
        }
    }

    public function paymentCancelledAction()
    {

    }

}