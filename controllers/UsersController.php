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
        if ($userInfo) {
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
        Session::destroy();
        $this->render('site/home');

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

        $this->render('users/profile', ['dbinfo' => $ads->getAdsByUserId($dataUser['id']), 'user' => $dataUser]);

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
        $users = new Users();
        $params = $this->getRequest()->getParams();
        $siteUrl = Registry::get('siteUrl');
        $paypalMode = Registry::get('paypal', 'mode');
        $paypalCurrencyCode = Registry::get('paypal', 'currencyCode');
        $paypalReturnURL = 'http://www.'.$siteUrl.'/payment/success';
        $paypalCancelURL = 'http://www.'.$siteUrl.'/payment/cancelled';

        $paypalmode = ($paypalMode == 'sandbox') ? '.sandbox' : '';

        if ($_POST) //Post Data received from product list page.
        {
            //Mainly we need 4 variables from product page Item Name, Item Price, Item Number and Item Quantity.

            //Please Note : People can manipulate hidden field amounts in form,
            //In practical world you must fetch actual price from database using item id. Eg:
            //$ItemPrice = $mysqli->query("SELECT item_price FROM products WHERE id = Product_Number");



            $ItemName = $_POST["itemname"]; //Item Name
            $ItemPrice = $_POST["itemprice"]; //Item Price
            $ItemDesc = $_POST["itemdesc"]; //Item Number
            $ItemQty = $_POST["itemqty"];

            //Parameters for SetExpressCheckout, which will be sent to PayPal
            $padata = '&METHOD=SetExpressCheckout' .
                '&RETURNURL=' . urlencode($paypalReturnURL) .
                '&CANCELURL=' . urlencode($paypalCancelURL) .
                '&PAYMENTREQUEST_0_PAYMENTACTION=' . urlencode("SALE") .

                '&L_PAYMENTREQUEST_0_NAME0=' . urlencode($ItemName) .
                '&L_PAYMENTREQUEST_0_DESC0=' . urlencode($ItemDesc) .
                '&L_PAYMENTREQUEST_0_AMT0=' . urlencode($ItemPrice) .
                '&L_PAYMENTREQUEST_0_QTY0=' . urlencode($ItemQty) .

                '&NOSHIPPING=0' . //set 1 to hide buyer's shipping address, in-case products that does not require shipping

                '&PAYMENTREQUEST_0_ITEMAMT=' . urlencode($ItemPrice) .
                '&PAYMENTREQUEST_0_SHIPPINGAMT=0' .
                '&PAYMENTREQUEST_0_AMT=' . urlencode($ItemPrice) .
                '&PAYMENTREQUEST_0_CURRENCYCODE=' . urlencode($paypalCurrencyCode);

            ############# set session variable we need later for "DoExpressCheckoutPayment" #######
            $_SESSION['ItemName'] = $ItemName; //Item Name
            $_SESSION['ItemPrice'] = $ItemPrice; //Item Price
            $_SESSION['ItemDesc'] = $ItemDesc; //Item Number
            $_SESSION['ItemQty'] = $ItemQty; // Item Quantity

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
                echo '<div style="color:red"><b>Error : </b>' . urldecode($httpParsedResponseAr["L_LONGMESSAGE0"]) . '</div>';
                echo '<pre>';
                print_r($httpParsedResponseAr);
                echo '</pre>';
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

                echo '<h2>Success</h2>';
                echo 'Your Transaction ID : ' . urldecode($httpParsedResponseAr["PAYMENTINFO_0_TRANSACTIONID"]);

                /*
                //Sometimes Payment are kept pending even when transaction is complete.
                //hence we need to notify user about it and ask him manually approve the transiction
                */

                if ('Completed' == $httpParsedResponseAr["PAYMENTINFO_0_PAYMENTSTATUS"]) {
                    echo '<div style="color:green">Payment Received! Your product will be sent to you very soon!</div>';
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

                    echo '<br /><b>Stuff to store in database :</b><br /><pre>';
                    /*
                    #### SAVE BUYER INFORMATION IN DATABASE ###
                    //see (http://www.sanwebe.com/2013/03/basic-php-mysqli-usage) for mysqli usage

                    $buyerName = $httpParsedResponseAr["FIRSTNAME"].' '.$httpParsedResponseAr["LASTNAME"];
                    $buyerEmail = $httpParsedResponseAr["EMAIL"];

                    //Open a new connection to the MySQL server
                    $mysqli = new mysqli('host','username','password','database_name');

                    //Output any connection error
                    if ($mysqli->connect_error) {
                        die('Error : ('. $mysqli->connect_errno .') '. $mysqli->connect_error);
                    }

                    $insert_row = $mysqli->query("INSERT INTO BuyerTable
                    (BuyerName,BuyerEmail,TransactionID,ItemName,ItemNumber, ItemAmount,ItemQTY)
                    VALUES ('$buyerName','$buyerEmail','$transactionID','$ItemName',$ItemNumber, $ItemTotalPrice,$ItemQTY)");

                    if($insert_row){
                        print 'Success! ID of last inserted record is : ' .$mysqli->insert_id .'<br />';
                    }else{
                        die('Error : ('. $mysqli->errno .') '. $mysqli->error);
                    }

                    */

                    echo '<pre>';
                    print_r($httpParsedResponseAr);
                    echo '</pre>';
                } else {
                    echo '<div style="color:red"><b>GetTransactionDetails failed:</b>' . urldecode($httpParsedResponseAr["L_LONGMESSAGE0"]) . '</div>';
                    echo '<pre>';
                    print_r($httpParsedResponseAr);
                    echo '</pre>';

                }

            } else {
                echo '<div style="color:red"><b>Error : </b>' . urldecode($httpParsedResponseAr["L_LONGMESSAGE0"]) . '</div>';
                echo '<pre>';
                print_r($httpParsedResponseAr);
                echo '</pre>';
            }
        }


    }

    public function paymentCancelledAction()
    {

    }

}