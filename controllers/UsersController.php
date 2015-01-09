<?php
use application\core\BaseController;
use application\classes\Session;
use application\classes\Paginator;
use application\classes\Registry;
use application\classes\Paypal;
use application\core\Error;

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
        if ($userInfo) {
            header('Location: /home');
        }

        if ($this->getRequest()->isPost()) {
            $res = $user->authorize(isset($_POST['rememberMe']));
            if (Users::isAuthorized()) {
                $data = $user->get();
                header('Location: /home');
                exit;
            }
        }
        $this->render('users/login', ['error' => $res]);
    }

    public function registrationAction()
    {
        $user = new Users();
        $data = $user->get();
        if ($data) {
            $this->render('site/home', ['user' => $data]);
        }

        if ($this->getRequest()->isPost()) {
            $res = $user->create();
            if ($res === true) {
                if (!$user->sendMail()) {
                    die('Не удалось отправить сообщение!!!');
                } else {
                    $messages = "На ваш почтовый адрес отправлено сообщение со ссылкой для активации аккаунта.";
                    $this->render('users/info', ['messages' => $messages]);
                    return;
                }
            } else {
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
        setcookie('PHPSESSID', '', time() - 3600);

        Session::destroy();
        header('Location: /home');

    }

    public function restorePasswordAction()
    {
        $user = new Users();
        if ($this->getRequest()->isPost()) {
            $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
            if (!$email) {
                $this->errors .= "Не правильно введен email!";
                $this->render('users/restore-password');
                return;
            }
            $userData = $user->getByEmail($email);
            if (!$userData) {
                $this->errors .= "Данный email не зарегистрирован на сайте!";
            } else {
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

        $category = new Category();
        $ads = new Ads($category);

        $paginator=new Paginator(['ads'=>$ads,'users'=>$users],2,3);


        $this->render('users/profile',['dbinfo'=>$paginator->getData(),'paginator'=>$paginator, 'user' => $dataUser]);

    }

    public function editAction()
    {
        $message = null;
        $users = new Users();
        $data = $users->get();
        if ($this->getRequest()->isPost()) {
            $error = $users->edit();
            if (!empty($error)) {
                $this->render('users/edit-profile', ['error' => $error, 'user' => $data]);
            }
            $message = "Информация успешно обновлена!";
            $users = new Users();
            $data = $users->get();
        }
        $this->render('users/edit-profile', ['user' => $data, 'message' => $message]);
    }

    public function subscribePaymentPlanAction()
    {
        $siteUrl = Registry::get('siteUrl');
        if(isset($_POST['signup']) || isset($_POST['buy'])) {
            header('Location: http://' . $_SERVER['SERVER_NAME'] . '/login');
            die;
        }
        $users = new Users();
        $plans = new Plans();
        $params = $this->getRequest()->getParams();

        $paypalMode = Registry::get('paypal', 'mode');
        $paypalCurrencyCode = Registry::get('paypal', 'currencyCode');
        $paypalReturnURL = 'http://'.$siteUrl.'/payment/success';
        $paypalCancelURL = 'http://'.$siteUrl.'/payment/cancelled';

        $paypalmode = ($paypalMode == 'sandbox') ? '.sandbox' : '';

        //Post Data received from product list page.
        if ($_POST) {
            $ItemPrice = $plans->getPriceByName($_POST["itemname"]);
            $ItemName = $_POST["itemname"]; //Item Name
            $ItemDesc = $_POST["itemdesc"]; //Item Description
            $ItemQty = $_POST["itemqty"];

            if($ItemName == 'free') {
                $payments = new Payments();
                $payments->saveTransaction(0, $ItemName);
                $this->render('users/info', ['messages' => 'Success!']);
                exit;
            }

            //Parameters for SetExpressCheckout, which will be sent to PayPal
            $padata = '&METHOD=SetExpressCheckout' .
                '&RETURNURL=' . urlencode($paypalReturnURL) .
                '&CANCELURL=' . urlencode($paypalCancelURL) .
                '&PAYMENTREQUEST_0_PAYMENTACTION=' . urlencode("SALE") .

                '&L_PAYMENTREQUEST_0_NAME0=' . urlencode($ItemName) .
                '&L_PAYMENTREQUEST_0_DESC0=' . urlencode($ItemDesc) .
                '&L_PAYMENTREQUEST_0_AMT0=' . $ItemPrice .
                '&L_PAYMENTREQUEST_0_QTY0=' . urlencode($ItemQty) .

                '&NOSHIPPING=0' . //set 1 to hide buyer's shipping address, in-case products that does not require shipping

                '&PAYMENTREQUEST_0_ITEMAMT=' . urlencode($ItemPrice) .
                '&PAYMENTREQUEST_0_SHIPPINGAMT=0' .
                '&PAYMENTREQUEST_0_AMT=' . urlencode($ItemPrice) .
                '&PAYMENTREQUEST_0_CURRENCYCODE=' . urlencode($paypalCurrencyCode);

            ############# set session variable we need later for "DoExpressCheckoutPayment" #######
            Session::set('ItemName', $ItemName);
            Session::set('ItemPrice', $ItemPrice);
            Session::set('ItemDesc', $ItemDesc);
            Session::set('ItemQty', $ItemQty);

            //execute the "SetExpressCheckOut" method to obtain paypal token
            $paypal = new Paypal();
            $httpParsedResponseAr = $paypal->PPHttpPost('SetExpressCheckout', $padata, $paypalMode);

            //Respond according to message we receive from Paypal
            if ("SUCCESS" == strtoupper($httpParsedResponseAr["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($httpParsedResponseAr["ACK"])) {

                //Redirect user to PayPal store with Token received.
                $paypalurl = 'https://www' . $paypalmode . '.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=' . $httpParsedResponseAr["TOKEN"] . '';
                header('Location: ' . $paypalurl);

            } else {
                //Show error message
                ob_start();
                echo '<div style="color:red"><b>Error : </b>' . urldecode($httpParsedResponseAr["L_LONGMESSAGE0"]) . '</div>';
                echo '<pre>';
                print_r($httpParsedResponseAr);
                echo '</pre>';
                $message = ob_get_clean();
                $error = new Error($message);
                $error->showMessages();
            }
        }

        //Paypal redirects back to this page using ReturnURL, We should receive TOKEN and Payer ID
        if (isset($_GET["token"]) && isset($_GET["PayerID"])) {
            //we will be using these two variables to execute the "DoExpressCheckoutPayment"
            //Note: we haven't received any payment yet.

            $token = $_GET["token"];
            $payer_id = $_GET["PayerID"];

            //get session variables

            $ItemName = $_SESSION['ItemName']; //Item Name
            $ItemPrice = $_SESSION['ItemPrice']; //Item Price
            $ItemDesc = $_SESSION['ItemDesc']; //Item Description
            $ItemQty = $_SESSION['ItemQty']; // Item Quantity

            $padata = '&TOKEN=' . urlencode($token) .
                '&PAYERID=' . urlencode($payer_id) .
                '&PAYMENTREQUEST_0_PAYMENTACTION=' . urlencode("SALE") .

                //set item info here, otherwise we won't see product details later
                '&L_PAYMENTREQUEST_0_NAME0=' . urlencode($ItemName) .
                '&L_PAYMENTREQUEST_0_DESC0=' . urlencode($ItemDesc) .
                '&L_PAYMENTREQUEST_0_AMT0=' . urlencode($ItemPrice) .
                '&L_PAYMENTREQUEST_0_QTY0=' . urlencode($ItemQty) .

                '&PAYMENTREQUEST_0_ITEMAMT=' . urlencode($ItemPrice) .
                '&PAYMENTREQUEST_0_SHIPPINGAMT=0' .
                '&PAYMENTREQUEST_0_AMT=' . urlencode($ItemPrice) .
                '&PAYMENTREQUEST_0_CURRENCYCODE=' . urlencode($paypalCurrencyCode);

            //execute the "DoExpressCheckoutPayment" at this point to Receive payment from user.
            $paypal = new paypal();
            $httpParsedResponseAr = $paypal->PPHttpPost('DoExpressCheckoutPayment', $padata, $paypalMode);

            //Check if everything went ok..
            if ("SUCCESS" == strtoupper($httpParsedResponseAr["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($httpParsedResponseAr["ACK"])) {
                ob_start();
                echo '<h2>Success</h2>';
                echo 'Your Transaction ID : ' . urldecode($httpParsedResponseAr["PAYMENTINFO_0_TRANSACTIONID"]);
                $transactionId = $httpParsedResponseAr["PAYMENTINFO_0_TRANSACTIONID"];

                /*
                //Sometimes Payment are kept pending even when transaction is complete.
                //hence we need to notify user about it and ask him manually approve the transiction
                */

                if ('Completed' == $httpParsedResponseAr["PAYMENTINFO_0_PAYMENTSTATUS"]) {
                    echo '<div style="color:green">Payment Received!</div>';
                } elseif ('Pending' == $httpParsedResponseAr["PAYMENTINFO_0_PAYMENTSTATUS"]) {
                    echo '<div style="color:red">Transaction Complete, but payment is still pending! ' .
                        'You need to manually authorize this payment in your <a target="_new" href="http://www.paypal.com">Paypal Account</a></div>';
                }

                // we can retrive transection details using either GetTransactionDetails or GetExpressCheckoutDetails
                // GetTransactionDetails requires a Transaction ID, and GetExpressCheckoutDetails requires Token returned by SetExpressCheckOut
                $padata = '&TOKEN=' . urlencode($token);
                $paypal = new paypal();
                $httpParsedResponseAr = $paypal->PPHttpPost('GetExpressCheckoutDetails', $padata, $paypalMode);

                if ("SUCCESS" == strtoupper($httpParsedResponseAr["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($httpParsedResponseAr["ACK"])) {

                    $payments = new Payments();
                    $payments->saveTransaction($transactionId, $httpParsedResponseAr['L_NAME0']);

                    $message = ob_get_clean();
                    $this->render('users/info', ['messages' => $message]);
                } else {
                    echo '<div style="color:red"><b>GetTransactionDetails failed:</b>' . urldecode($httpParsedResponseAr["L_LONGMESSAGE0"]) . '</div>';
                    echo '<pre>';
                    print_r($httpParsedResponseAr);
                    echo '</pre>';
                    $message = ob_get_clean();
                    $this->render('users/info', ['messages' => $message]);

                }

            } else {
                ob_start();
                echo '<div style="color:red"><b>Error : </b>' . urldecode($httpParsedResponseAr["L_LONGMESSAGE0"]) . '</div>';
                echo '<pre>';
                print_r($httpParsedResponseAr);
                echo '</pre>';
                $message = ob_get_clean();
                $this->render('users/info', ['messages' => $message]);

            }
        }


    }

    public function paymentCancelledAction()
    {
        $this->render('users/info', ['messages' => 'Payment has been canceled']);
    }

}