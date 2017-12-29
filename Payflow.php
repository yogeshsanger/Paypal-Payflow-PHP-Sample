<?php

namespace PaypalPayflow;

class Payflow {

    private $environment = 'sandbox';
    private $liveUrl = 'https://payflowpro.paypal.com';
    private $testUrl = 'https://pilot-payflowpro.paypal.com';
    private $vps_timeout = 45;
    private $partner = '';
    private $vendor = '';
    private $user = '';
    private $password = '';
    private $TRXTYPE = 'S';
    private $TENDER = 'C';
    private $currency = 'USD';
    public $data = [];

    public function setEnv($env) {
        $this->environment = $env;
    }
    
    public function setCurrency($currency) {
        $this->currency = $currency;
    }

    public function setVendor($vendor) {
        $this->vendor = $vendor;
    }

    public function setPartner($partner) {
        $this->partner = $partner;
    }

    public function setUser($user) {
        $this->user = $user;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    public function pay() {
        $response = [];
        $this->__setPayload();
        $payload = $this->__getPayload();
        $headers = array();
        $headers[] = "Content-Type: text/namevalue"; //or text/xml if using XMLPay.
        $headers[] = "Content-Length: " . strlen($payload);  // Length of data to be passed 
        $headers[] = "X-VPS-Timeout: {$this->vps_timeout}";
        $headers[] = "X-VPS-Request-ID:" . uniqid(rand(), true);
        $headers[] = "X-VPS-VIT-Client-Type: PHP/cURL";          // What you are using
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->__getUrl());
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($ch, CURLOPT_HEADER, 1);                // tells curl to include headers in response
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);        // return into a variable
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);              // times out after 90 secs
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);        // this line makes it work under https
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);        //adding POST data
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);       //verifies ssl certificate
        curl_setopt($ch, CURLOPT_FORBID_REUSE, TRUE);       //forces closure of connection when done
        curl_setopt($ch, CURLOPT_POST, 1);
        $result = curl_exec($ch);
        $this->__getResponseArr($result, $response);
        if($response['RESULT'] == 0) {
            return array('success' => 1,'message' => 'approved','data' => $response);
        }
        return array('success' => 0,'message' => 'Failed','data' => $response);
    }

    private function __getEnv() {
        return $this->environment;
    }

    private function __setPayload() {
        $this->data['VENDOR'] = $this->vendor;
        $this->data['PARTNER'] = $this->partner;
        $this->data['USER'] = $this->user;
        $this->data['PWD'] = $this->password;
        $this->data['TENDER'] = $this->TENDER;
        $this->data['TRXTYPE'] = $this->TRXTYPE;
        $this->data['CURRENCY'] = $this->currency;
    }

    private function __getPayload() {
        $response = [];
        foreach ($this->data as $key => $item) {
            $response[] = $key . '[' . strlen($item) . ']=' . $item;
        }
        return implode('&', $response);
    }

    private function __getUrl() {
        if ($this->__getEnv() == 'sandbox') {
            return $this->testUrl;
        }
        return $this->liveUrl;
    }

    private function __getResponseArr($data, &$response) {
        $dataSting = strstr($data, "RESULT");
        $dataStingArr = explode('&', $dataSting);
        foreach ($dataStingArr as $item) {
            $itemArr = explode('=', $item);
            $response[reset($itemArr)] = end($itemArr);
        }
    }
    
    public function paypalResponseMessage($resposneKey) {
        $data = [
            '0' => 'Approved',
            '1' => 'User authentication failed',
            '2' => 'Invalid tender type. Your merchant bank account does not support the following credit card type that was submitted.',
            '3' => 'Invalid transaction type. Transaction type is not appropriate for this transaction. For example, you cannot credit an authorisation-only transaction.',
            '4' => 'Invalid amount format. Use the format: \'#####.##\'  Do not include currency symbols or commas.',
            '5' => 'Invalid merchant information. Processor does not recognise your merchant account information. Contact your bank account acquirer to resolve this problem.',
            '6' => 'Invalid or unsupported currency code',
            '7' => 'Field format error. Invalid information entered. See RESPMSG.',
            '8' => 'Not a transaction server',
            '9' => 'Too many parameters or invalid stream',
            '10' => 'Too many line items',
            '11' => 'Client time-out waiting for response',
            '12' => 'Declined. Check the credit card number, expiry date and transaction information to make sure they were entered correctly. If this does not resolve the problem, have the customer call their card issuing bank to resolve.',
            '13' => 'Referral. Transaction cannot be approved electronically but can be approved with a verbal authorisation. Contact your merchant bank to obtain an authorisation and submit a manual Voice Authorisation transaction.',
            '14' => 'Invalid Client Certification ID. Check the HTTP header. If the tag, X-VPS-VIT-CLIENT-CERTIFICATION-ID, is missing, RESULT code 14 is returned.',
            '19' => 'Original transaction ID not found. The transaction ID you entered for this transaction is not valid. See RESPMSG.',
            '20' => 'Cannot find the customer reference number',
            '22' => 'Invalid ABA number',
            '23' => 'Invalid account number. Check credit card number and re-submit.',
            '24' => 'Invalid expiry date. Check and re-submit.',
            '25' => 'Invalid Host Mapping. You are trying to process a tender type such as Discover Card, but you are not set up with your merchant bank to accept this card type.',
            '26' => 'Invalid vendor account',
            '27' => 'Insufficient partner permissions',
            '28' => 'Insufficient user permissions',
            '29' => 'Invalid XML document. This could be caused by an unrecognised XML tag or a bad XML format that cannot be passed by the system.',
            '30' => 'Duplicate transaction',
            '31' => 'Error in adding the recurring profile',
            '32' => 'Error in modifying the recurring profile',
            '33' => 'Error in cancelling the recurring profile',
            '34' => 'Error in forcing the recurring profile',
            '35' => 'Error in reactivating the recurring profile',
            '36' => 'OLTP Transaction failed',
            '37' => 'Invalid recurring profile ID',
            '50' => 'Insufficient funds available in account',
            '99' => 'General error. See RESPMSG.',
            '100' => 'Transaction type not supported by host',
            '101' => 'Time-out value too small',
            '102' => 'Processor not available',
            '103' => 'Error reading response from host',
            '104' => 'Timeout waiting for processor response. Try your transaction again.',
            '105' => 'Credit error. Make sure you have not already credited this transaction, or that this transaction ID is for a creditable transaction. (For example, you cannot credit an authorisation.)',
            '106' => 'Host not available',
            '107' => 'Duplicate suppression time-out',
            '108' => 'Void error. See RESPMSG. Make sure the transaction ID entered has not already been voided. If not, then look at the Transaction Detail screen for this transaction to see if it has settled. (The Batch field is set to a number greater than zero if the transaction has been settled.) If the transaction has already settled, your only recourse is a reversal (credit a payment or submit a payment for a credit).',
            '109' => 'Time-out waiting for host response',
            '111' => 'Capture error. Either an attempt to capture a transaction that is not an authorisation transaction type, or an attempt to capture an authorisation transaction that has already been captured.',
            '112' => 'Failed AVS check. Address and postcode do not match. An authorisation may still exist on the cardholder’s account.',
            '114' => 'Card Security Code mismatch. An authorisation may still exist on the cardholder’s account.',
            '115' => 'System busy, try again later',
            '116' => 'PayPal Internal error. Failed to lock terminal number',
            '117' => 'Failed merchant rule check',
            '118' => 'Invalid keywords found in string fields',
            '131' => 'Version 1 Website Payments Pro SDK client no longer supported. Upgrade to the most recent version of the Website Payments Pro client. ',
            '150' => 'Issuing bank timed out',
            '151' => 'Issuing bank unavailable',
            '1000' => 'Generic host error. This is a generic message returned by your credit card processor. The RESPMSG will contain more information describing the error. '
        ];
        $response = isset($data[$resposneKey]) ? $data[$resposneKey] : 'Invalid Request';
        return $response;
    }

}
