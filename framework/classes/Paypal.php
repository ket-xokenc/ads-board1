<?php
namespace application\classes;

use application\classes\Registry;
use application\core\Error;

/**
 * Class Paypal
 * @package application\classes
 */
class Paypal
{

    function PPHttpPost($methodName_, $nvpStr_, $PayPalMode)
    {
        // Set up your API credentials, PayPal end point, and API version.
        $API_UserName = Registry::get('paypal', 'apiUserName');
        $API_Password = Registry::get('paypal', 'apiPassword');
        $API_Signature = Registry::get('paypal', 'apiSignature');

        $paypalmode = ($PayPalMode == 'sandbox') ? '.sandbox' : '';

        $API_Endpoint = "https://api-3t" . $paypalmode . ".paypal.com/nvp";
        $version = urlencode('109.0');

        // Set the curl parameters.
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $API_Endpoint);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);

        // Turn off the server and peer verification (TrustManager Concept).
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);

        // Set the API operation, version, and API signature in the request.
        $nvpreq = "METHOD=$methodName_&VERSION=$version&PWD=$API_Password&USER=$API_UserName&SIGNATURE=$API_Signature$nvpStr_";

        // Set the request as a POST FIELD for curl.
        curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);

        // Get response from the server.
        $httpResponse = curl_exec($ch);

        if (!$httpResponse) {
            exit("$methodName_ failed: " . curl_error($ch) . '(' . curl_errno($ch) . ')');
            $error = new Error("$methodName_ failed: " . curl_error($ch) . '(' . curl_errno($ch) . ')');
            $error->showMessages();
            die;
        }

        // Extract the response details.
        $httpResponseAr = explode("&", $httpResponse);

        $httpParsedResponseAr = array();
        foreach ($httpResponseAr as $i => $value) {
            $tmpAr = explode("=", $value);
            if (sizeof($tmpAr) > 1) {
                $httpParsedResponseAr[$tmpAr[0]] = $tmpAr[1];
            }
        }

        if ((0 == sizeof($httpParsedResponseAr)) || !array_key_exists('ACK', $httpParsedResponseAr)) {
            $error = new Error("Invalid HTTP Response for POST request($nvpreq) to $API_Endpoint.");
            $error->showMessages();
            die;
        }

        return $httpParsedResponseAr;
    }

}
